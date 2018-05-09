<?php
namespace benf\embeddedassets\models;

use JsonSerializable;

use Twig_Markup;

use Craft;
use craft\base\Model;
use craft\helpers\Template;
use craft\validators\StringValidator;
use craft\validators\UrlValidator;

use benf\embeddedassets\Plugin as EmbeddedAssets;
use benf\embeddedassets\validators\Image as ImageValidator;
use benf\embeddedassets\validators\TwigMarkup as TwigMarkupValidator;

/**
 * Class EmbeddedAsset
 * @package benf\embeddedassets\models
 */
class EmbeddedAsset extends Model implements JsonSerializable
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
	 * @var \Twig_Markup
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
			[['title', 'description', 'authorName', 'providerName', 'publishedTime', 'license'], StringValidator::class],
			[['url', 'image', 'authorUrl', 'providerIcon', 'providerUrl'], UrlValidator::class, 'defaultScheme' => 'https'],
			['type', 'in', 'range' => ['link', 'image', 'video', 'rich']],
			['type', 'default', 'value' => 'link'],
			['tags', 'each', 'rule' => [StringValidator::class]],
			[['feeds'], 'each', 'rule' => [UrlValidator::class]],
			[['width', 'height', 'aspectRatio', 'imageWidth', 'imageHeight'], 'number', 'min' => 0],
			[['images', 'providerIcons'], 'each', 'rule' => [ImageValidator::class]],
			['code', TwigMarkupValidator::class],
		];
	}

	/**
	 * A JSON serializable copy of this model.
	 * Used when saving to file.
	 *
	 * @return array
	 */
	public function jsonSerialize()
	{
		// Disable recursion since it interferes with Twig_Markup instances and causes `code` values to be lost.
		return $this->toArray([], [], false);
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
	 * Gets the HTML for the embedded asset.
	 * This method automatically checks if the embed code is safe to use. If it is, then the embed code is returned.
	 * Otherwise, if the embedded asset is not a "link" type and it has an image, an <img> tag is returned. Otherwise,
	 * an <a> link tag is returned.
	 *
	 * @return Twig_Markup
	 */
	public function getHtml(): Twig_Markup
	{
		if ($this->code && $this->isSafe())
		{
			$html = $this->code;
		}
		else if ($this->type !== 'link' && $this->image)
		{
			$html = Template::raw("<img src=\"$this->image\" alt=\"$this->title\" width=\"$this->imageWidth\" height=\"$this->imageHeight\">");
		}
		else
		{
			$html = Template::raw("<a href=\"$this->url\" target=\"_blank\" rel=\"noopener\">$this->title</a>");
		}

		return $html;
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

	//
	// Deprecated properties

	/**
	 * @deprecated
	 * @return string
	 */
	public function getRequestUrl()
	{
		Craft::$app->getDeprecator()->log('EmbeddedAsset::getRequestUrl', "The embedded asset property `requestUrl` is now deprecated. Use the `url` property instead.");

		return $this->url;
	}

	/**
	 * @deprecated
	 * @return null
	 */
	public function getCacheAge()
	{
		Craft::$app->getDeprecator()->log('EmbeddedAsset::getCacheAge', "The embedded asset property `cacheAge` is now deprecated.");

		return null;
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public function getThumbnailUrl()
	{
		Craft::$app->getDeprecator()->log('EmbeddedAsset::getThumbnailUrl', "The embedded asset property `thumbnailUrl` is now deprecated. Use the `image` property instead.");

		return $this->image;
	}

	/**
	 * @deprecated
	 * @return number
	 */
	public function getThumbnailWidth()
	{
		Craft::$app->getDeprecator()->log('EmbeddedAsset::getThumbnailWidth', "The embedded asset property `thumbnailWidth` is now deprecated. Use the `imageWidth` property instead.");

		return $this->imageWidth;
	}

	/**
	 * @deprecated
	 * @return number
	 */
	public function getThumbnailHeight()
	{
		Craft::$app->getDeprecator()->log('EmbeddedAsset::getThumbnailHeight', "The embedded asset property `thumbnailHeight` is now deprecated. Use the `imageHeight` property instead.");

		return $this->imageHeight;
	}

	/**
	 * @deprecated
	 * @return null|\Twig_Markup
	 */
	public function getSafeHtml()
	{
		Craft::$app->getDeprecator()->log('EmbeddedAsset::getSafeHtml', "The embedded asset property `safeHtml` is now deprecated. Use a combination of the `isSafe()` method and the `code` property instead.");

		return $this->isSafe() ? $this->code : null;
	}
}
