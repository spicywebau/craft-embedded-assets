<?php

namespace Craft;

function move_uploaded_file($filename, $destination)
{
	return \move_uploaded_file($filename, $destination) || rename($filename, $destination);
}

class EmbeddedAssetsService extends BaseApplicationComponent
{
	public function parseUrl($url)
	{
		$essence = new \Essence\Essence();

		return $essence->extract($url);
	}

	public function saveAsset(EmbeddedAssetsModel $media, $folderId)
	{
		$isExisting = false;
		$record = null;

		if(is_int($media->id))
		{
			$record = EmbeddedAssetsRecord::model()->findById($media->id);

			if($record)
			{
				$isExisting = true;
			}
			else
			{
				throw new Exception(Craft::t('No embedded asset exists with the ID "{id}".', array('id' => $media->id)));
			}
		}
		else
		{
			$record = EmbeddedAssetsRecord::model()->findByAttributes(array(
				'assetId' => $media->assetId,
			));

			if($record)
			{
				$isExisting = true;
			}
			else
			{
				$record = new EmbeddedAssetsRecord();
			}
		}

		$asset = craft()->assets->getFileById($media->assetId);

		if(!$asset)
		{
			$asset = $this->_storeFile($media, $folderId);
		}

		if($asset)
		{
			$record->assetId = $asset->id;
		}

		$record->type            = $media->type;
		$record->version         = $media->version;
		$record->url             = $media->url;
		$record->title           = $media->title;
		$record->description     = $media->description;
		$record->authorName      = $media->authorName;
		$record->authorUrl       = $media->authorUrl;
		$record->providerName    = $media->providerName;
		$record->providerUrl     = $media->providerUrl;
		$record->cacheAge        = $media->cacheAge;
		$record->thumbnailUrl    = $media->thumbnailUrl;
		$record->thumbnailWidth  = $media->thumbnailWidth;
		$record->thumbnailHeight = $media->thumbnailHeight;
		$record->html            = $media->html;
		$record->width           = $media->width;
		$record->height          = $media->height;

		$record->validate();
		$media->addErrors($record->getErrors());

		$success = !$media->hasErrors();

		if($success)
		{
			$event = new Event($this, array(
				'media'      => $media,
				'isNewEmbed' => !$isExisting,
			));

			$this->onBeforeSaveEmbed($event);

			if($event->performAction)
			{
				// Create transaction only if this isn't apart of an already occurring transaction
				$transaction = craft()->db->getCurrentTransaction() ? false : craft()->db->beginTransaction();

				try
				{
					$record->save(false);
					$media->id = $record->id;

					if($transaction)
					{
						$transaction->commit();
					}
				}
				catch(\Exception $e)
				{
					if($transaction)
					{
						$transaction->rollback();
					}

					throw $e;
				}

				$this->onSaveEmbed(new Event($this, array(
					'media'      => $media,
					'isNewEmbed' => !$isExisting,
				)));
			}
		}

		return $success;
	}

	private function _storeFile(EmbeddedAssetsModel $media, $folderId)
	{
		if($media->thumbnailUrl)
		{
			$this->_addToFiles('assets-upload', $media->thumbnailUrl);

			$response = craft()->assets->uploadFile($folderId);

			if($response->isSuccess())
			{
				$fileId = $response->getDataItem('fileId');
				$file = craft()->assets->getFileById($fileId);

				return $file;
			}
		}

		return false;
	}

	/**
	 * @param $key
	 * @param $url
	 * @see http://stackoverflow.com/a/13915285/556609
	 */
	private function _addToFiles($key, $url)
	{
		$tempName = tempnam('/tmp', 'php_files');
		$originalName = basename(parse_url($url, PHP_URL_PATH));

		$imgRawData = file_get_contents($url);
		file_put_contents($tempName, $imgRawData);

		$_FILES[$key] = array(
			'name'     => $originalName,
			'type'     => mime_content_type($tempName),
			'tmp_name' => $tempName,
			'error'    => 0,
			'size'     => strlen($imgRawData),
		);
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
