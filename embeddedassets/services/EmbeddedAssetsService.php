<?php

namespace Craft;

EmbeddedAssetsPlugin::loadDependencies();

/**
 * This hack overrides the `move_uploaded_file()` function in the Craft namespace, and allows it to support files
 * injected into the `$_FILES` array.
 *
 * The `move_uploaded_files()` function, as stated in the PHP docs, will only work if the file was submitted through a
 * POST request, which isn't the case when injecting. The override calls another similar function that doesn't have this
 * restriction if the original function fails.
 *
 * @param $filename
 * @param $destination
 * @return bool
 */
function move_uploaded_file($filename, $destination)
{
	return \move_uploaded_file($filename, $destination) || rename($filename, $destination);
}

class EmbeddedAssetsService extends BaseApplicationComponent
{
	private $_purifier = null;
	private $_ogProperties = array(
		'title' => \Opengraph\Opengraph::OG_TITLE,
		'description' => \Opengraph\Opengraph::OG_DESCRIPTION,
		'providerName' => \Opengraph\Opengraph::OG_SITE_NAME,
		'thumbnailUrl' => \Opengraph\Opengraph::OG_IMAGE,
		'thumbnailWidth' => \Opengraph\Opengraph::OG_IMAGE_WIDTH,
		'thumbnailHeight' => \Opengraph\Opengraph::OG_IMAGE_HEIGHT,
	);

	public function __construct()
	{
		require_once Craft::getPathOfAlias('system.vendors.htmlpurifier') . '/HTMLPurifier.standalone.php';

		$whitelist = EmbeddedAssetsPlugin::getWhitelist();

		foreach($whitelist as $i => $url)
		{
			$whitelist[$i] = preg_quote($url);
		}

		$regexp = '%^(https?:)?//([a-z0-9\-]+\.)?(' . implode('|', $whitelist) . ')([:/].*)?$%';

		$config = \HTMLPurifier_Config::createDefault();
		$config->set('HTML.SafeIframe', true);
		$config->set('URI.SafeIframeRegexp', $regexp);
		$config->set('Cache.SerializerPath', \Yii::app()->getRuntimePath());
		$this->_purifier = new \HTMLPurifier($config);
	}

	public function parseUrl($url)
	{
		$essence = new \Essence\Essence();
		$options = EmbeddedAssetsPlugin::getParameters();

		EmbeddedAssetsPlugin::log("Requesting URL \"{$url}\"");

		$result = $essence->extract($url, $options);

		if($result && $result->html)
		{
			EmbeddedAssetsPlugin::log("Embed data found");

			$properties = array();

			foreach($result as $property => $value)
			{
				if(empty($value) && isset($this->_ogProperties[$property]))
				{
					$properties[] = $property;
				}
			}

			if(!empty($properties))
			{
				EmbeddedAssetsPlugin::log("Some data missing - looking for related Open Graph metadata");

				try
				{
					$data = $this->_readExternalFile($url);

					if(!$data)
					{
						throw new \Exception("Could not read data");
					}

					$reader = new \Opengraph\Reader();
					$reader->parse($data);

					foreach($properties as $property)
					{
						$ogProperty = $this->_ogProperties[$property];
						$result->$property = $reader->getMeta($ogProperty);
					}
				}
				catch(\Exception $e)
				{
					EmbeddedAssetsPlugin::log("Error requesting/parsing Open Graph metadata (\"{$e->getMessage()}\")", LogLevel::Warning);
				}
			}
		}

		return $result;
	}

	public function getEmbeddedAsset(AssetFileModel $asset)
	{
		$embed = EmbeddedAssetsModel::populateFromAsset($asset);

		return $embed ? $embed : null;
	}

	public function getEmbeddedAssets($assets, $indexBy = null)
	{
		$embeds = array();

		foreach($assets as $i => $asset)
		{
			$embed = $this->getEmbeddedAsset($asset);

			if($embed)
			{
				if($indexBy)
				{
					$embeds[$asset->$indexBy] = $embed;
				}
				else
				{
					$embeds[] = $embed;
				}
			}
		}

		return $embeds;
	}

	public function saveEmbeddedAsset(EmbeddedAssetsModel $media, $folderId)
	{
		$event = new Event($this, array(
			'media' => $media,
		));

		// Purify HTML is necessary
		if($media->html && !$media->safeHtml)
		{
			$media->safeHtml = $this->_purifier->purify($media->html);
		}

		$this->onBeforeSaveEmbed($event);

		if($event->performAction)
		{
			// Create transaction only if this isn't apart of an already occurring transaction
			$transaction = craft()->db->getCurrentTransaction() ? false : craft()->db->beginTransaction();

			try
			{
				$asset = $this->_storeFile($media, $folderId);
				$asset->getContent()->title = $media->title;

				craft()->assets->storeFile($asset);

				$media->id = $asset->id;

				if($transaction)
				{
					$transaction->commit();
				}
			}
			catch(\Exception $e)
			{
				EmbeddedAssetsPlugin::log("Error saving embedded asset (\"{$e->getMessage()}\")", LogLevel::Error);

				if($transaction)
				{
					$transaction->rollback();
				}

				throw $e;
			}

			$cacheKey = EmbeddedAssetsPlugin::getCacheKey();
			craft()->cache->delete($cacheKey);

			$this->onSaveEmbed(new Event($this, array(
				'media' => $media,
				'asset' => $asset,
			)));
		}

		return true;
	}

	public function readAssetFile(AssetFileModel $asset)
	{
		$url = $asset->getUrl();

		if(!UrlHelper::isAbsoluteUrl($url))
		{
			$protocol = craft()->request->isSecureConnection() ? 'https' : 'http';
			$url = UrlHelper::getUrlWithProtocol($url, $protocol);
		}

		return $this->_readExternalFile($url);
	}

	private function _storeFile(EmbeddedAssetsModel $media, $folderId)
	{
		$fileLabel = substr(preg_replace('/[^a-z0-9]+/i', '-', $media->getTitle()), 0, 40);
		$filePrefix = EmbeddedAssetsPlugin::getFileNamePrefix();
		$fileExtension = '.json';
		$fileName = $filePrefix . $fileLabel . $fileExtension;

		$existingFile = craft()->assets->findFile(array(
			'folderId' => $folderId,
			'filename' => $fileName
		));

		if($existingFile)
		{
			EmbeddedAssetsPlugin::log("File with name \"{$fileName}\" already exists in this location");

			$fileUniqueId = DateTimeHelper::currentUTCDateTime()->format('ymd_His');
			$fileName = $filePrefix . $fileLabel . '_' . $fileUniqueId . $fileExtension;
		}

		$fileData = $media->getAttributes(null, true);
		$fileData['__embeddedasset__'] = true;
		unset($fileData['id']);
		unset($fileData['settings']);

		$this->_addToFiles('assets-upload', $fileName, JsonHelper::encode($fileData), 'application/json');

		$response = craft()->assets->uploadFile($folderId);

		if($response->isSuccess())
		{
			$fileId = $response->getDataItem('fileId');
			$file = craft()->assets->getFileById($fileId);

			return $file;
		}
		else
		{
			throw new \Exception($response->errorMessage);
		}
	}

	/**
	 * @param $key
	 * @param $url
	 * @param $data
	 * @param $mimeType - A fallback mime type in case it can't be detected
	 * @see http://stackoverflow.com/a/13915285/556609
	 */
	private function _addToFiles($key, $url, $data = null, $mimeType = 'text/plain')
	{
		$tempName = tempnam(craft()->getRuntimePath() . '/temp', 'embedded_assets_');
		$originalName = basename(parse_url($url, PHP_URL_PATH));

		$fileData = (is_string($data) ? $data : file_get_contents($url));
		file_put_contents($tempName, $fileData);

		if(function_exists('finfo_open'))
		{
			EmbeddedAssetsPlugin::log("Setting embedded asset file mime type with `finfo`");

			$fileInfo = finfo_open(FILEINFO_MIME);
			$mimeType = finfo_file($fileInfo, $tempName);
			finfo_close($fileInfo);
		}
		else if(function_exists('mime_content_type'))
		{
			EmbeddedAssetsPlugin::log("Setting embedded asset file mime type with `mime_content_type`");

			$mimeType = mime_content_type($tempName);
		}

		$_FILES[$key] = array(
			'name'     => $originalName,
			'type'     => $mimeType,
			'tmp_name' => $tempName,
			'error'    => 0,
			'size'     => strlen($fileData),
		);
	}

	/**
	 * Reads in data from an external link.
	 *
	 * @param $url
	 * @return bool|mixed|string
	 */
	private function _readExternalFile($url)
	{
		if(function_exists('curl_init'))
		{
			EmbeddedAssetsPlugin::log("Reading file with `curl`");

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

			$data = curl_exec($ch);
			$error = curl_error($ch);

			if(!empty($error))
			{
				EmbeddedAssetsPlugin::log("Error reading file (\"{$error}\")", LogLevel::Error);

				$data = false;
			}

			curl_close($ch);

			return $data;
		}

		$allowUrlFopen = preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen'));
		if($allowUrlFopen)
		{
			EmbeddedAssetsPlugin::log("Reading file with `file_get_contents`");

			return @file_get_contents($url);
		}

		return false;
	}

	/**
	 * An event dispatcher for the moment before saving an embed.
	 *
	 * @param Event $event
	 * @throws \CException
	 */
	public function onBeforeSaveEmbed(Event $event)
	{
		$this->raiseEvent('onBeforeSaveEmbed', $event);
	}

	/**
	 * An event dispatcher for the moment after saving an embed.
	 *
	 * @param Event $event
	 * @throws \CException
	 */
	public function onSaveEmbed(Event $event)
	{
		$this->raiseEvent('onSaveEmbed', $event);
	}
}
