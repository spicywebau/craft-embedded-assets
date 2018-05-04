<?php
namespace benf\embeddedassets;

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

	public function saveAsset(EmbeddedAsset $embeddedAsset)
	{

	}

	public function checkWhitelist(string $url): bool
	{
		return true;
	}

	private function _adapterToModel(Adapter $adapter): EmbeddedAsset
	{
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
}
