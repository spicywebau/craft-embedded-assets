<?php
namespace spicyweb\embeddedassets;

use DOMDocument;

use yii\base\Component;
use yii\base\Exception;
use yii\base\ErrorException;

use Twig_Markup;

use Craft;
use craft\elements\Asset;
use craft\models\VolumeFolder;
use craft\helpers\Template;
use craft\helpers\StringHelper;
use craft\helpers\Json;
use craft\helpers\Assets;
use craft\helpers\FileHelper;

use Embed\Embed;
use Embed\Adapters\Adapter;

use spicyweb\embeddedassets\Plugin as EmbeddedAssets;
use spicyweb\embeddedassets\models\EmbeddedAsset;

/**
 * Class Service
 *
 * @package spicyweb\embeddedassets
 * @author Spicy Web <craft@spicyweb.com.au>
 * @author Benjamin Fleming
 * @since 1.0.0
 */
class Service extends Component
{
	/**
	 * Requests embed data from a URL.
	 *
	 * @param string $url
	 * @return EmbeddedAsset
	 */
	public function requestUrl(string $url): EmbeddedAsset
	{
		$cacheService = Craft::$app->getCache();

		$cacheKey = 'embeddedasset:' . $url;
		$embeddedAsset = $cacheService->get($cacheKey);

		if (!$embeddedAsset)
		{
			$pluginSettings = EmbeddedAssets::$plugin->getSettings();

			$options = [
				'min_image_width' => $pluginSettings->minImageSize,
				'min_image_height' => $pluginSettings->minImageSize,
				'oembed' => ['parameters' => []],
			];

			if (!empty($pluginSettings->parameters))
			{
				foreach ($pluginSettings->parameters as $parameter)
				{
					$param = $parameter['param'];
					$value = $parameter['value'];
					$options['oembed']['parameters'][$param] = $value;
				}
			}

			if ($pluginSettings->embedlyKey) $options['oembed']['embedly_key'] = $pluginSettings->embedlyKey;
			if ($pluginSettings->iframelyKey) $options['oembed']['iframely_key'] = $pluginSettings->iframelyKey;
			if ($pluginSettings->googleKey) $options['google'] = ['key' => $pluginSettings->googleKey];
			if ($pluginSettings->soundcloudKey) $options['soundcloud'] = ['key' => $pluginSettings->soundcloudKey];
			if ($pluginSettings->facebookKey) $options['facebook'] = ['key' => $pluginSettings->facebookKey];

			$adapter = Embed::create($url, $options);
			$array = $this->_convertFromAdapter($adapter);
			$embeddedAsset = $this->createEmbeddedAsset($array);

			$cacheService->set($cacheKey, $embeddedAsset, $pluginSettings->cacheDuration);
		}

		return $embeddedAsset;
	}

	/**
	 * Checks a URL against the whitelist set on the plugin settings model.
	 *
	 * @param string $url
	 * @return bool Whether the URL is in the whitelist.
	 */
	public function checkWhitelist(string $url): bool
	{
		$pluginSettings = EmbeddedAssets::$plugin->getSettings();
		$whitelist = array_merge($pluginSettings->whitelist, $pluginSettings->extraWhitelist);

		foreach ($whitelist as $whitelistUrl)
		{
			if ($whitelistUrl)
			{
				$pattern = explode('*', $whitelistUrl);
				$pattern = array_map('preg_quote', $pattern);
				$pattern = implode('[a-z][a-z0-9]*', $pattern);
				$pattern = "%^(https?:)?//([a-z0-9\-]+\\.)?$pattern([:/].*)?$%";

				if (preg_match($pattern, $url))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Retrieves an embedded asset model from an asset element, if one exists.
	 *
	 * @param Asset $asset
	 * @return EmbeddedAsset|null
	 * @throws \yii\base\InvalidConfigException
	 * @throws \craft\errors\AssetException
	 */
	public function getEmbeddedAsset(Asset $asset)
	{
		$embeddedAsset = null;

		if ($asset->kind === Asset::KIND_JSON)
		{
			try
			{
				// Note - 2018-05-09
				// As of Craft 3.0.6 this can be replaced with $asset->getContents()
				// This version was released on 2018-05-08 so need to wait for majority adoption
				$fileContents = stream_get_contents($asset->getStream());
				$decodedJson = Json::decodeIfJson($fileContents);

				if (is_array($decodedJson))
				{
					$embeddedAsset = $this->createEmbeddedAsset($decodedJson);
				}
			}
			catch (Exception $e)
			{
				// Ignore errors and assume it's not an embedded asset
				$embeddedAsset = null;
			}
		}

		return $embeddedAsset;
	}

	/**
	 * Creates an embedded asset model from an array of property/value pairs.
	 * Returns the model unless the array included any properties that aren't defined on the model.
	 *
	 * @param array $array
	 * @return EmbeddedAsset|null
	 */
	private function createEmbeddedAsset(array $array)
	{
		$embeddedAsset = new EmbeddedAsset();

		$isLegacy = isset($array['__embeddedasset__']);
		if ($isLegacy)
		{
			$array = $this->_convertFromLegacy($array);
		}

		foreach ($array as $key => $value)
		{
			if (!$embeddedAsset->hasProperty($key))
			{
				return null;
			}

			switch ($key)
			{
				case 'code':
				{
					$code = $value instanceof Twig_Markup ? (string)$value :
						is_string($value) ? $value : '';

					$embeddedAsset->$key = empty($code) ? null : Template::raw($code);
				}
				break;
				default:
				{
					$embeddedAsset->$key = $value;
				}
			}
		}

		// Attempts to extract missing dimensional properties from the embed code
		$dimensions = $this->_getDimensions($embeddedAsset);
		$embeddedAsset->width = $dimensions[0];
		$embeddedAsset->height = $dimensions[1];

		// Sets aspect ratio is missing
		if (!$embeddedAsset->aspectRatio && $embeddedAsset->width && $embeddedAsset->height)
		{
			$embeddedAsset->aspectRatio = $embeddedAsset->height / $embeddedAsset->width * 100;
		}

		return $embeddedAsset->validate() ? $embeddedAsset : null;
	}

	/**
	 * Creates an asset element ready to be saved from an embedded asset model.
	 *
	 * @param EmbeddedAsset $embeddedAsset
	 * @param VolumeFolder $folder The folder to save the asset to.
	 * @return Asset
	 * @throws \craft\errors\AssetLogicException
	 * @throws \yii\base\ErrorException
	 * @throws \yii\base\Exception
	 */
	public function createAsset(EmbeddedAsset $embeddedAsset, VolumeFolder $folder): Asset
	{
		$hasReplaceMb4 = method_exists(StringHelper::class, 'replaceMb4');

		$assetsService = Craft::$app->getAssets();
		$pluginSettings = EmbeddedAssets::$plugin->getSettings();

		$tempFilePath = Assets::tempFilePath();
		$fileContents = Json::encode($embeddedAsset, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

		FileHelper::writeToFile($tempFilePath, $fileContents);

		$assetTitle = $embeddedAsset->title ?: $embeddedAsset->url;

		// Ensure the title contains no emoji
		if ($hasReplaceMb4)
		{
			$assetTitle = StringHelper::replaceMb4($assetTitle, '');
		}
		else if (StringHelper::containsMb4($assetTitle))
		{
			$assetTitle = preg_replace_callback('/./u', function(array $match): string
			{
				return strlen($match[0]) >= 4 ? '' : $match[0];
			}, $assetTitle);
		}

		$fileName = Assets::prepareAssetName($assetTitle, false);
		$fileName = str_replace('.', '', $fileName);
		$fileName = $fileName ?: 'embedded-asset';
		$fileName = StringHelper::safeTruncate($fileName, $pluginSettings->maxFileNameLength) . '.json';
		$fileName = $assetsService->getNameReplacementInFolder($fileName, $folder->id);

		$asset = new Asset();
		$asset->title = StringHelper::safeTruncate($assetTitle, $pluginSettings->maxAssetNameLength);
		$asset->tempFilePath = $tempFilePath;
		$asset->filename = $fileName;
		$asset->newFolderId = $folder->id;
		$asset->volumeId = $folder->volumeId;
		$asset->avoidFilenameConflicts = true;
		$asset->setScenario(Asset::SCENARIO_CREATE);

		return $asset;
	}

	/**
	 * Checks an embedded asset embed code for URL's that are safe (or in other words, are whitelisted).
	 *
	 * @param EmbeddedAsset $embeddedAsset
	 * @return bool
	 */
	public function isEmbedSafe(EmbeddedAsset $embeddedAsset): bool
	{
		$isSafe = $this->checkWhitelist($embeddedAsset->url);

		if ($isSafe)
		{
			$dom = $this->getEmbedCode($embeddedAsset);

			if ($dom)
			{
				foreach ($dom->getElementsByTagName('iframe') as $iframeElement)
				{
					$href = $iframeElement->getAttribute('href');
					$isSafe = $isSafe && (!$href || $this->checkWhitelist($href));
				}

				foreach ($dom->getElementsByTagName('script') as $scriptElement)
				{
					$src = $scriptElement->getAttribute('src');
					$content = $scriptElement->textContent;

					// Inline scripts are impossible to analyse for safety, so just assume they're all evil
					$isSafe = $isSafe && !$content && (!$src || $this->checkWhitelist($src));
				}
			}
		}

		return $isSafe;
	}

	/**
	 * Gets the embed code as DOM, if one exists and is valid.
	 *
	 * @param EmbeddedAsset $embeddedAsset
	 * @return DOMDocument|null
	 */
	public function getEmbedCode(EmbeddedAsset $embeddedAsset)
	{
		$dom = null;

		if ($embeddedAsset->code)
		{
			$errors = libxml_use_internal_errors(true);
			$entities = libxml_disable_entity_loader(true);

			try
			{
				$dom = new DOMDocument();
				$code = "<div>$embeddedAsset->code</div>";
				$isHtml = $dom->loadHTML($code, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

				if (!$isHtml)
				{
					throw new ErrorException();
				}
			}
			catch (ErrorException $e)
			{
				// Corrupted code property, like due to invalid HTML.
				$dom = null;
			}
			finally
			{
				libxml_use_internal_errors($errors);
				libxml_disable_entity_loader($entities);
			}
		}

		return $dom;
	}

	/**
	 * Gets the HTML for the embedded asset.
	 * This method automatically checks if the embed code is safe to use. If it is, then the embed code is returned.
	 * Otherwise, if the embedded asset is not a "link" type and it has an image, an <img> tag is returned. Otherwise,
	 * an <a> link tag is returned.
	 *
	 * @param EmbeddedAsset $embeddedAsset
	 * @return Twig_Markup
	 */
	public function getEmbedHtml(EmbeddedAsset $embeddedAsset): Twig_Markup
	{
		if ($embeddedAsset->code && $embeddedAsset->isSafe())
		{
			$html = $embeddedAsset->code;
		}
		else if ($embeddedAsset->type !== 'link' && $embeddedAsset->image)
		{
			$html = Template::raw("<img src=\"$embeddedAsset->image\" alt=\"$embeddedAsset->title\" width=\"$embeddedAsset->imageWidth\" height=\"$embeddedAsset->imageHeight\">");
		}
		else
		{
			$html = Template::raw("<a href=\"$embeddedAsset->url\" target=\"_blank\" rel=\"noopener\">$embeddedAsset->title</a>");
		}

		return $html;
	}

	/**
	 * Returns the image from an embedded asset closest to some size.
	 * It favours images that most minimally exceed the supplied size.
	 *
	 * @param EmbeddedAsset $embeddedAsset
	 * @param int $size
	 * @return array|null
	 */
	public function getImageToSize(EmbeddedAsset $embeddedAsset, int $size)
	{
		return is_array($embeddedAsset->images) ?
			$this->_getImageToSize($embeddedAsset->images, $size) : null;
	}

	/**
	 * Returns the provider icon from an embedded asset closest to some size.
	 * It favours icons that most minimally exceed the supplied size.
	 *
	 * @param EmbeddedAsset $embeddedAsset
	 * @param int $size
	 * @return array|null
	 */
	public function getProviderIconToSize(EmbeddedAsset $embeddedAsset, int $size)
	{
		return is_array($embeddedAsset->providerIcons) ?
			$this->_getImageToSize($embeddedAsset->providerIcons, $size) : null;
	}

	/**
	 * Gets the width/height of an embedded asset.
	 * Attempts to extract missing dimensional properties from the embed code.
	 *
	 * @param EmbeddedAsset $embeddedAsset
	 * @return array
	 */
	private function _getDimensions(EmbeddedAsset $embeddedAsset): array
	{
		$width = $embeddedAsset->width;
		$height = $embeddedAsset->height;

		if (!$width || !$height)
		{
			$dom = $this->getEmbedCode($embeddedAsset);

			if ($dom)
			{
				$iframeElement = $dom->getElementsByTagName('iframe')->item(0);

				if ($iframeElement)
				{
					$width = $iframeElement->getAttribute('width');
					$height = $iframeElement->getAttribute('height');
					$style = $iframeElement->getAttribute('style');

					$matches = [];
					$matchCount = preg_match_all('/(width|height):\s*([0-9]+(\.[0-9]+)?)px/i', $style, $matches);

					for ($i = 0; $i < $matchCount; $i++)
					{
						$styleProperty = strtolower($matches[1][$i]);
						$styleValue = $matches[2][$i];

						switch ($styleProperty)
						{
							case 'width': $width = $styleValue; break;
							case 'height': $height = $styleValue; break;
						}
					}

					$width = $width ? floatval($width) : null;
					$height = $height ? floatval($height) : null;
				}
			}
		}

		return [ $width, $height ];
	}

	/**
	 * Helper method for retrieving an image closest to some size.
	 *
	 * @param array $images
	 * @param int $size
	 * @return array|null
	 */
	private function _getImageToSize(array $images, int $size)
	{
		$selectedImage = null;
		$selectedSize = 0;

		foreach ($images as $image)
		{
			if (is_array($image))
			{
				$imageWidth = isset($image['width']) && is_numeric($image['width']) ? $image['width'] : 0;
				$imageHeight = isset($image['height']) && is_numeric($image['height']) ? $image['height'] : 0;
				$imageSize = max($imageWidth, $imageHeight);

				if (!$selectedImage ||
					($selectedSize < $size && $imageSize > $selectedSize) ||
					($selectedSize > $imageSize && $imageSize > $size))
				{
					$selectedImage = $image;
					$selectedSize = $imageSize;
				}
			}
		}

		return $selectedImage;
	}

	/**
	 * Helper method for filtering images to some minimum size.
	 * Used to filter out small images that likely are absolutely useless.
	 *
	 * @param array $image
	 * @return bool
	 */
	private function _isImageLargeEnough(array $image)
	{
		$pluginSettings = EmbeddedAssets::$plugin->getSettings();
		$minImageSize = $pluginSettings->minImageSize;

		return $image['width'] >= $minImageSize && $image['height'] >= $minImageSize;
	}

	/**
	 * Creates an "embedded asset ready" array from an adapter.
	 *
	 * @param Adapter $adapter
	 * @return array
	 */
	private function _convertFromAdapter(Adapter $adapter): array
	{
		return [
			'title' => $adapter->title,
			'description' => $adapter->description,
			'url' => $adapter->url,
			'type' => $adapter->type,
			'tags' => $adapter->tags,
			'images' => array_filter($adapter->images, [$this, '_isImageLargeEnough']),
			'image' => $adapter->image,
			'imageWidth' => $adapter->imageWidth,
			'imageHeight' => $adapter->imageHeight,
			'code' => Template::raw($adapter->code ?: ''),
			'width' => $adapter->width,
			'height' => $adapter->height,
			'aspectRatio' => $adapter->aspectRatio,
			'authorName' => $adapter->authorName,
			'authorUrl' => $adapter->authorUrl,
			'providerName' => $adapter->providerName,
			'providerUrl' => $adapter->providerUrl,
			'providerIcons' => array_filter($adapter->providerIcons, [$this, '_isImageLargeEnough']),
			'providerIcon' => $adapter->providerIcon,
			'publishedTime' => $adapter->publishedTime,
			'license' => $adapter->license,
			'feeds' => $adapter->feeds,
		];
	}

	/**
	 * Creates an "embedded asset ready" array from an array matching the Craft 2 version of the plugin.
	 *
	 * @param array $legacy
	 * @return array
	 */
	private function _convertFromLegacy(array $legacy): array
	{
		$width = intval($legacy['width'] ?? 0);
		$height = intval($legacy['height'] ?? 0);
		$imageUrl = $legacy['thumbnailUrl'] ?? null;
		$imageWidth = intval($legacy['thumbnailWidth'] ?? 0);
		$imageHeight = intval($legacy['thumbnailHeight'] ?? 0);

		return [
			'title' => $legacy['title'] ?? null,
			'description' => $legacy['description'] ?? null,
			'url' => $legacy['url'] ?? null,
			'type' => $legacy['type'] ?? null,
			'images' => $imageUrl ? [[
				'url' => $imageUrl,
				'width' => $imageWidth,
				'height' => $imageHeight,
				'size' => $imageWidth * $imageHeight,
				'mime' => null,
			]] : [],
			'image' => $imageUrl,
			'imageWidth' => $imageWidth,
			'imageHeight' => $imageHeight,
			'code' => Template::raw($legacy['html'] ?? $legacy['safeHtml'] ?? ''),
			'width' => $width,
			'height' => $height,
			'aspectRatio' => $width > 0 ? $height / $width * 100 : 0,
			'authorName' => $legacy['authorName'] ?? null,
			'authorUrl' => $legacy['authorUrl'] ?? null,
			'providerName' => $legacy['providerName'] ?? null,
			'providerUrl' => $legacy['providerUrl'] ?? null,
		];
	}
}
