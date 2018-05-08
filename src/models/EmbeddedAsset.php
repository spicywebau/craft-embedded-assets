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
	 * @var string required
	 */
	public $title;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string URL required
	 */
	public $url;

	/**
	 * @var string link|image|video\rich required
	 */
	public $type;

	/**
	 * @var array of strings
	 */
	public $tags;

	/**
	 * @var array of URLs
	 */
	public $feeds;

	/**
	 * @var array of images
	 */
	public $images;

	/**
	 * @var string URL
	 */
	public $image;

	/**
	 * @var number
	 */
	public $imageWidth;

	/**
	 * @var number
	 */
	public $imageHeight;

	/**
	 * @var string HTML
	 */
	public $code;

	/**
	 * @var number
	 */
	public $width;

	/**
	 * @var number
	 */
	public $height;

	/**
	 * @var number
	 */
	public $aspectRatio;

	/**
	 * @var string
	 */
	public $authorName;

	/**
	 * @var string URL
	 */
	public $authorUrl;

	/**
	 * @var array of images
	 */
	public $providerIcons;

	/**
	 * @var string URL
	 */
	public $providerIcon;

	/**
	 * @var string
	 */
	public $providerName;

	/**
	 * @var string URL
	 */
	public $providerUrl;

	/**
	 * @var string
	 */
	public $publishedTime;

	/**
	 * @var string
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
	 * Method wrapper for Service::isEmbedSafe
	 *
	 * @return bool
	 */
	public function isSafe(): bool
	{
		return EmbeddedAssets::$plugin->methods->isEmbedSafe($this);
	}

	/**
	 * Method wrapper for Service::getImageToSize
	 *
	 * @param int $size
	 * @return mixed
	 */
	public function getImageToSize(int $size)
	{
		return EmbeddedAssets::$plugin->methods->getImageToSize($this, $size);
	}

	/**
	 * Method wrapper for Service::getProviderIconToSize
	 *
	 * @param int $size
	 * @return mixed
	 */
	public function getProviderIconToSize(int $size)
	{
		return EmbeddedAssets::$plugin->methods->getProviderIconToSize($this, $size);
	}
}
