<?php
namespace benf\embeddedassets\models;

use craft\base\Model;
use craft\validators\StringValidator;
use craft\validators\UrlValidator;

use benf\embeddedassets\Plugin as EmbeddedAssets;
use benf\embeddedassets\validators\Image as ImageValidator;

/**
 * Class EmbeddedAsset
 * @package benf\embeddedassets\models
 */
class EmbeddedAsset extends Model
{
	/**
	 * @var
	 */
	public $title;

	/**
	 * @var
	 */
	public $description;

	/**
	 * @var
	 */
	public $url;

	/**
	 * @var
	 */
	public $type;

	/**
	 * @var
	 */
	public $tags;

	/**
	 * @var
	 */
	public $feeds;

	/**
	 * @var
	 */
	public $images;

	/**
	 * @var
	 */
	public $image;

	/**
	 * @var
	 */
	public $imageWidth;

	/**
	 * @var
	 */
	public $imageHeight;

	/**
	 * @var
	 */
	public $code;

	/**
	 * @var
	 */
	public $width;

	/**
	 * @var
	 */
	public $height;

	/**
	 * @var
	 */
	public $aspectRatio;

	/**
	 * @var
	 */
	public $authorName;

	/**
	 * @var
	 */
	public $authorUrl;

	/**
	 * @var
	 */
	public $providerIcons;

	/**
	 * @var
	 */
	public $providerIcon;

	/**
	 * @var
	 */
	public $providerName;

	/**
	 * @var
	 */
	public $providerUrl;

	/**
	 * @var
	 */
	public $publishedTime;

	/**
	 * @var
	 */
	public $license;

	/**
	 * @return array
	 */
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

	/**
	 * @return bool
	 */
	public function isSafe(): bool
	{
		return EmbeddedAssets::$plugin->methods->isEmbedSafe($this);
	}

	/**
	 * @param int $size
	 * @return mixed
	 */
	public function getImageToSize(int $size)
	{
		return EmbeddedAssets::$plugin->methods->getImageToSize($this, $size);
	}

	/**
	 * @param int $size
	 * @return mixed
	 */
	public function getProviderIconToSize(int $size)
	{
		return EmbeddedAssets::$plugin->methods->getProviderIconToSize($this, $size);
	}
}
