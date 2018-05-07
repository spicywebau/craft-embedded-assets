<?php
namespace benf\embeddedassets;

use yii\base\Component;

use Craft;
use craft\base\LocalVolumeInterface;
use craft\elements\Asset;
use craft\helpers\Json;

use Embed\Embed;
use Embed\Adapters\Adapter;

use benf\embeddedassets\Plugin as EmbeddedAssets;
use benf\embeddedassets\models\EmbeddedAsset;

class Service extends Component
{
	public function requestUrl(string $url): EmbeddedAsset
	{
		$cacheService = Craft::$app->getCache();

		$cacheKey = 'embeddedassets:' . $url;
		$embeddedAsset = $cacheService->get($cacheKey);

		if (!$embeddedAsset)
		{
			$pluginSettings = EmbeddedAssets::$plugin->getSettings();

			$options = [
				'min_image_width' => $pluginSettings->minImageSize,
				'min_image_height' => $pluginSettings->minImageSize,
				'oembed' => ['parameters' => []],
			];

			foreach($pluginSettings->parameters as $parameter)
			{
				$param = $parameter['param'];
				$value = $parameter['value'];
				$options['oembed']['parameters'][$param] = $value;
			}

			if($pluginSettings->embedlyKey) $options['oembed']['embedly_key'] = $pluginSettings->embedlyKey;
			if($pluginSettings->iframelyKey) $options['oembed']['iframely_key'] = $pluginSettings->iframelyKey;
			if($pluginSettings->googleKey) $options['google'] = ['key' => $pluginSettings->googleKey];
			if($pluginSettings->soundcloudKey) $options['soundcloud'] = ['key' => $pluginSettings->soundcloudKey];
			if($pluginSettings->facebookKey) $options['facebook'] = ['key' => $pluginSettings->facebookKey];

			$adapter = Embed::create($url, $options);
			$embeddedAsset = $this->_adapterToModel($adapter);

			$cacheService->set($cacheKey, $embeddedAsset, $pluginSettings->cacheDuration);
		}

		return $embeddedAsset;
	}

	public function checkWhitelist(string $url): bool
	{
		$pluginSettings = EmbeddedAssets::$plugin->getSettings();

		foreach ($pluginSettings->whitelist as $whitelistUrl)
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

		return false;
	}

	public function getEmbeddedAsset(Asset $asset)
	{
		$embeddedAsset = null;

		if ($asset->kind === Asset::KIND_JSON)
		{
			$assetVolume = $asset->getVolume();
			$assetPath = $assetVolume instanceof LocalVolumeInterface ?
				$assetVolume->getRootPath() . DIRECTORY_SEPARATOR . $asset->getPath() :
				$asset->getCopyOfFile();

			if (file_exists($assetPath))
			{
				$fileContents = file_get_contents($assetPath);
				$decodedJson = Json::decodeIfJson($fileContents);

				if (is_array($decodedJson))
				{
					$isLegacyFile = isset($decodedJson['__embeddedasset__']);
					if ($isLegacyFile)
					{
						$decodedJson = $this->_convertFromLegacy($decodedJson);
					}

					$embeddedAsset = $this->_arrayToModel($decodedJson);
				}
			}
		}

		return $embeddedAsset;
	}

	private function _adapterToModel(Adapter $adapter): EmbeddedAsset
	{
		return new EmbeddedAsset([
			'title' => $adapter->title,
			'description' => $adapter->description,
			'url' => $adapter->url,
			'type' => $adapter->type,
			'tags' => $adapter->tags,
			'images' => array_filter($adapter->images, [$this, '_isImageLargeEnough']),
			'image' => $adapter->image,
			'imageWidth' => $adapter->imageWidth,
			'imageHeight' => $adapter->imageHeight,
			'code' => $adapter->code,
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
		]);
	}

	private function _arrayToModel(array $array)
	{
		$embeddedAsset = new EmbeddedAsset();

		foreach ($array as $key => $value)
		{
			if (!$embeddedAsset->hasProperty($key))
			{
				return null;
			}

			$embeddedAsset->$key = $value;
		}

		return $embeddedAsset->validate() ? $embeddedAsset : null;
	}

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
			'images' => $imageUrl ? [
				[
					'url' => $imageUrl,
					'width' => $imageWidth,
					'height' => $imageHeight,
					'size' => $imageWidth * $imageHeight,
					'mime' => null,
				]
			] : [],
			'image' => $imageUrl,
			'imageWidth' => $imageWidth,
			'imageHeight' => $imageHeight,
			'code' => $legacy['html'] ?? $legacy['safeHtml'] ?? null,
			'width' => $width,
			'height' => $height,
			'aspectRatio' => $width > 0 ? $height / $width * 100 : 0,
			'authorName' => $legacy['authorName'] ?? null,
			'authorUrl' => $legacy['authorUrl'] ?? null,
			'providerName' => $legacy['providerName'] ?? null,
			'providerUrl' => $legacy['providerUrl'] ?? null,
		];
	}

	private function _isImageLargeEnough(array $image)
	{
		$pluginSettings = EmbeddedAssets::$plugin->getSettings();
		$minImageSize = $pluginSettings->minImageSize;

		return $image['width'] >= $minImageSize && $image['height'] >= $minImageSize;
	}
}
