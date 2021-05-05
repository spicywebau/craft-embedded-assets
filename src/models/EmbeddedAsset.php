<?php

namespace spicyweb\embeddedassets\models;

use JsonSerializable;

use Craft;
use craft\base\Model;
use craft\helpers\Template;
use craft\validators\StringValidator;
use craft\validators\UrlValidator;
use spicyweb\embeddedassets\Plugin as EmbeddedAssets;
use spicyweb\embeddedassets\validators\Image as ImageValidator;
use spicyweb\embeddedassets\validators\TwigMarkup as TwigMarkupValidator;
use Twig\Markup as TwigMarkup;
use yii\base\Exception;

/**
 * Class EmbeddedAsset
 *
 * @package spicyweb\embeddedassets\models
 * @author Spicy Web <craft@spicyweb.com.au>
 * @author Benjamin Fleming
 * @since 1.0.0
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
     * @var TwigMarkup
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
            [
                ['title', 'description', 'authorName', 'providerName', 'publishedTime', 'license'],
                StringValidator::class
            ],
            [
                ['url', 'image', 'authorUrl', 'providerIcon', 'providerUrl'],
                UrlValidator::class,
                'defaultScheme' => 'https'
            ],
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
        // Disable recursion since it interferes with TwigMarkup instances and causes `code` values to be lost.
        return $this->toArray([], [], false);
    }

    /**
     * Method wrapper for Service::isEmbedSafe
     *
     * @since 2.4.0
     * @return bool
     */
    public function getIsSafe(): bool
    {
        return EmbeddedAssets::$plugin->methods->isEmbedSafe($this);
    }

    /**
     * Method wrapper for Service::getEmbedHtml
     *
     * @return TwigMarkup
     */
    public function getHtml(): TwigMarkup
    {
        return EmbeddedAssets::$plugin->methods->getEmbedHtml($this);
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

    /**
     * Returns the URL with additional params passed. Has to be type of video.
     *
     * @return string
     */
    public function getVideoUrl($params)
    {
        $url = null;

        if ($this->type == "video" && is_array($params)) {
            $url = $this->getMatchedVideoUrl();
            $url = $this->_addParamsToUrl($params, $url);
        }

        return $url;
    }

    /**
     * Returns the raw code with additional params passed. Has to be type of video.
     *
     * @param array $params
     * @return string
     */
    public function getVideoCode(array $params)
    {
        if ($this->type !== 'video') {
            throw new Exception('Tried to call getVideoCode() on an embedded asset with a type other than video');
        }

        $oldUrl = $this->getMatchedVideoUrl();
        $newUrl = $this->_addParamsToUrl($params, $oldUrl);
        $code = str_replace($oldUrl, $newUrl, $this->code);

        return Template::raw($code);
    }

    /**
     * Gets this embedded asset's video ID, if the embedded asset is a video.
     *
     * @since 2.2.3
     * @return string|null the video ID, or null if the embedded asset is not a video
     */
    public function getVideoId() {
        if ($this->type !== "video") {
            return null;
        }

        $url = explode('/', $this->getMatchedVideoUrl());

        if (in_array($this->providerName, ['YouTube', 'Vimeo'])) {
            return explode('?', $url[4])[0];
        }

        return null;
    }

    /**
     * Returns the modified url with params added.
     *
     * @return string
     */
    private function _addParamsToUrl($newParams, $pUrl, $overrideParams = false)
    {
        if ($overrideParams) {
            $startPos = strpos($pUrl, '?');
            $newUrl = substr($pUrl, 0, $startPos);
            $oldParams = $startPos !== false ? explode('&', substr($pUrl, $startPos + 1)): [];
            $params = [];
            $joinedParams = [];

            foreach ($oldParams as $param) {
                $split = explode('=', $param);
                $params[$split[0]] = $split[1] ?? '';
            }

            foreach ($newParams as $param) {
                $split = explode('=', $param);
                $params[$split[0]] = $split[1] ?? '';
            }

            foreach ($params as $key => $value) {
                $joinedParams[] = $key . ($value !== '' ? '=' . $value : '');
            }

            return $newUrl . (!empty($joinedParams) ? '?' . implode('&', $joinedParams) : '');
        } else {
            $url = (strpos($pUrl, '?') === false) ? $pUrl . '?' : $pUrl;

            foreach ($newParams as $param) {
                if (is_string($param)) {
                    $url = $url . '&' . $param;
                }
            }

            return $url;
        }
    }

    /**
     * Returns the embedded video URL.
     *
     * @return string
     */
    private function getMatchedVideoUrl()
    {
        preg_match('/src="([^"]+)"/', $this->code, $match);

        return $match[1];
    }

    //
    // Deprecated properties

    /**
     * Method wrapper for Service::isEmbedSafe
     *
     * @deprecated in 2.4.0, will be removed in 3.0.0; use `getIsSafe()` instead
     * @return bool
     */
    public function isSafe(): bool
    {
        return $this->getIsSafe();
    }

    /**
     * @return string
     * @deprecated
     */
    public function getRequestUrl()
    {
        Craft::$app->getDeprecator()->log('EmbeddedAsset::getRequestUrl',
            "The embedded asset property `requestUrl` is now deprecated. Use the `url` property instead.");

        return $this->url;
    }

    /**
     * @return null
     * @deprecated
     */
    public function getCacheAge()
    {
        Craft::$app->getDeprecator()->log('EmbeddedAsset::getCacheAge',
            "The embedded asset property `cacheAge` is now deprecated.");

        return null;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getThumbnailUrl()
    {
        Craft::$app->getDeprecator()->log('EmbeddedAsset::getThumbnailUrl',
            "The embedded asset property `thumbnailUrl` is now deprecated. Use the `image` property instead.");

        return $this->image;
    }

    /**
     * @return number
     * @deprecated
     */
    public function getThumbnailWidth()
    {
        Craft::$app->getDeprecator()->log('EmbeddedAsset::getThumbnailWidth',
            "The embedded asset property `thumbnailWidth` is now deprecated. Use the `imageWidth` property instead.");

        return $this->imageWidth;
    }

    /**
     * @return number
     * @deprecated
     */
    public function getThumbnailHeight()
    {
        Craft::$app->getDeprecator()->log('EmbeddedAsset::getThumbnailHeight',
            "The embedded asset property `thumbnailHeight` is now deprecated. Use the `imageHeight` property instead.");

        return $this->imageHeight;
    }

    /**
     * @return null|TwigMarkup
     * @deprecated
     */
    public function getSafeHtml()
    {
        Craft::$app->getDeprecator()->log('EmbeddedAsset::getSafeHtml',
            "The embedded asset property `safeHtml` is now deprecated. Use a combination of the `isSafe()` method and the `code` property instead.");

        return $this->isSafe() ? $this->code : null;
    }
}
