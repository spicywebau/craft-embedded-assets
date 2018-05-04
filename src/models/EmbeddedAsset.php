<?php
namespace benf\embeddedassets\models;

use craft\base\Model;

use benf\embeddedassets\Plugin as EmbeddedAssets;

class EmbeddedAsset extends Model
{
	public $title;
	public $description;
	public $url;
	public $type;
	public $tags;
	public $feeds;
	public $images;
	public $image;
	public $imageWidth;
	public $imageHeight;
	public $code;
	public $width;
	public $height;
	public $aspectRatio;
	public $authorName;
	public $authorUrl;
	public $providerIcons;
	public $providerIcon;
	public $providerName;
	public $providerUrl;
	public $publishedTime;
	public $license;
	public $linkedData;

	public function isSafe(): bool
	{
		$isSafe = EmbeddedAssets::$plugin->methods->checkWhitelist($this->url);

		if ($this->code)
		{
			$errors = libxml_use_internal_errors(true);
			$entities = libxml_disable_entity_loader(true);

			$dom = new \DOMDocument();
			$dom->loadHTML($this->code);

			libxml_use_internal_errors($errors);
			libxml_disable_entity_loader($entities);

			foreach ($dom->getElementsByTagName('iframe') as $iframeElement)
			{
				$href = $iframeElement->getAttribute('href');
				$isSafe = $isSafe && (!$href || EmbeddedAssets::$plugin->methods->checkWhitelist($href));
			}

			foreach ($dom->getElementsByTagName('script') as $scriptElement)
			{
				$src = $scriptElement->getAttribute('src');
				$content = $scriptElement->textContent;

				// Inline scripts are impossible to analyse for safety, so just assume they're all evil
				$isSafe = $isSafe && !$content && (!$src || EmbeddedAssets::$plugin->methods->checkWhitelist($src));
			}
		}

		return $isSafe;
	}
}
