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
		return EmbeddedAssets::$plugin->methods->isEmbedSafe($this);
	}

	public function getImageToSize(int $size)
	{
		return EmbeddedAssets::$plugin->methods->getImageToSize($this, $size);
	}

	public function getProviderIconToSize(int $size)
	{
		return EmbeddedAssets::$plugin->methods->getProviderIconToSize($this, $size);
	}
}
