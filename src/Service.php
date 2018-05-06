<?php
namespace benf\embeddedassets;

use Craft;
use craft\base\LocalVolumeInterface;
use craft\elements\Asset;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use yii\base\Component;

use Embed\Embed;
use Embed\Adapters\Adapter;

use benf\embeddedassets\Plugin as EmbeddedAssets;
use benf\embeddedassets\models\EmbeddedAsset;

class Service extends Component
{
	public function requestUrl(string $url): EmbeddedAsset
	{
		$options = [
			'oembed' => [
				'parameters' => [],
			],
		];

		$pluginSettings = EmbeddedAssets::$plugin->getSettings();

		foreach ($pluginSettings->parameters as $parameter)
		{
			$param = $parameter['param'];
			$value = $parameter['value'];
			$options['oembed']['parameters'][$param] = $value;
		}

		if ($pluginSettings->embedlyKey) $options['oembed']['embedly_key'] = $pluginSettings->embedlyKey;
		if ($pluginSettings->iframelyKey) $options['oembed']['iframely_key'] = $pluginSettings->iframelyKey;
		if ($pluginSettings->googleKey) $options['google'] = ['key' => $pluginSettings->googleKey];
		if ($pluginSettings->soundcloudKey) $options['soundcloud'] = ['key' => $pluginSettings->soundcloudKey];
		if ($pluginSettings->facebookKey) $options['facebook'] = ['key' => $pluginSettings->facebookKey];

		$adapter = Embed::create($url, $options);

		return $this->_adapterToModel($adapter);
	}

	public function checkWhitelist(string $url): bool
	{
		// TODO
		return true;
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
					$embeddedAsset = $this->_arrayToModel($decodedJson);
				}
			}
		}

		return $embeddedAsset;
	}

	private function _adapterToModel(Adapter $adapter): EmbeddedAsset
	{
		// TODO strip tags on everything (unless embed/embed already does it)
		return new EmbeddedAsset([
			'title' => $adapter->title,
			'description' => $adapter->description,
			'url' => $adapter->url,
			'type' => $adapter->type,
			'tags' => $adapter->tags,
			'images' => $adapter->images,
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
			'providerIcons' => $adapter->providerIcons,
			'providerIcon' => $adapter->providerIcon,
			'publishedTime' => $adapter->publishedTime,
			'license' => $adapter->license,
			'linkedData' => $adapter->linkedData,
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
}
