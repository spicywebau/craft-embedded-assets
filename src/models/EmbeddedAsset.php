<?php
namespace benf\embeddedassets\models;

use craft\base\Model;
use craft\validators\StringValidator;
use craft\validators\UrlValidator;

use benf\embeddedassets\Plugin as EmbeddedAssets;
use benf\embeddedassets\validators\Image as ImageValidator;

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

	public function rules()
	{
		return [
			[['title', 'url', 'type'], 'required'],
			[['title', 'description', 'authorName', 'code', 'providerName', 'publishedTime', 'license'], StringValidator::class],
			[['url', 'image', 'authorUrl', 'providerIcon', 'providerUrl'], UrlValidator::class, 'defaultScheme' => 'https'],
			['type', 'in', 'range' => ['link', 'image', 'video', 'rich']],
			['type', 'default', 'value' => 'link'],
			['tags', 'each', 'rule' => [StringValidator::class]],
			[['feeds'], 'each', 'rule' => [UrlValidator::class]],
			[['width', 'height', 'aspectRatio', 'imageWidth', 'imageHeight'], 'number', 'min' => 0],
			[['images', 'providerIcons'], 'each', 'rule' => [ImageValidator::class]],
		];
	}

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

	public function getImageToSize(int $size)
	{
		return is_array($this->images) ? $this->_getImageToSize($this->images, $size) : null;
	}

	public function getProviderIconToSize(int $size)
	{
		return is_array($this->providerIcons) ? $this->_getImageToSize($this->providerIcons, $size) : null;
	}

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
}
