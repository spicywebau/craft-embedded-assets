<?php

namespace spicyweb\embeddedassets\models;

use JsonSerializable;

use Twig_Markup;

use Craft;
use craft\base\Model;
use craft\validators\StringValidator;
use craft\validators\UrlValidator;
use craft\helpers\Template;

use spicyweb\embeddedassets\Plugin as EmbeddedAssets;
use spicyweb\embeddedassets\validators\Image as ImageValidator;
use spicyweb\embeddedassets\validators\TwigMarkup as TwigMarkupValidator;

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
     * Method wrapper for Service::getEmbedHtml
     *
     * @return Twig_Markup
     */
    public function getHtml(): Twig_Markup
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
     * @return string
     */
    public function getVideoUrl($params)
    {
        $url = null;
        
        if ($this->type == "video" && is_array($params)) {
            $url = $this->getMatchedVideoUrl();
            $url = $this->addParamsToVideoUrl($params, $url);
        }
        
        return $url;
    }
    
    /**
     * Returns the raw code with additional params passed. Has to be type of video.
     * @return string
     */
    public function getVideoCode($params)
    {
        $url = null;
        $code = null;
        
        if ($this->type == "video" && is_array($params)) {
            $url = $this->getMatchedVideoUrl();
            $originalUrl = $url;
            $code = $this->code;
            
            $url = $this->addParamsToVideoUrl($params, $url);
            
            $code = str_replace($originalUrl, $url, $code);
        }
        
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
     * @return string
     */
    private function addParamsToVideoUrl($arr, $pUrl)
    {
        $url = (strpos('?', $pUrl) === false) ? $pUrl . '?' : $pUrl;
        
        $paramsLength = count($arr);
        
        if ($paramsLength > 0) {
            for ($i = 0; $i < $paramsLength; $i++) {
                if (is_string($arr[$i])) {
                    $url = $url . '&' . $arr[$i];
                }
            }
        }
        
        return $url;
    }
    
    /**
     * Returns the embedded video url.
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
     * @return null|\Twig_Markup
     * @deprecated
     */
    public function getSafeHtml()
    {
        Craft::$app->getDeprecator()->log('EmbeddedAsset::getSafeHtml',
            "The embedded asset property `safeHtml` is now deprecated. Use a combination of the `isSafe()` method and the `code` property instead.");
        
        return $this->isSafe() ? $this->code : null;
    }
}
