<?php

namespace spicyweb\embeddedassets\models;

use Craft;

use craft\base\Model;
use craft\helpers\Html as HtmlHelper;
use craft\helpers\Template;
use craft\validators\StringValidator;
use craft\validators\UrlValidator;
use JsonSerializable;
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
    public string $title;

    /**
     * @var string
     */
    public string $description;

    /**
     * @var string URL required
     */
    public string $url;

    /**
     * @var string link|image|video\rich required
     */
    public string $type;

    /**
     * @var string[]|null
     */
    public ?array $tags = null;

    /**
     * @var string|null
     */
    public ?array $feeds = null;

    /**
     * @var array of images
     */
    public array $images;

    /**
     * @var string URL
     */
    public string $image;

    /**
     * @var number
     */
    public int $imageWidth;

    /**
     * @var number
     */
    public int $imageHeight;

    /**
     * @var TwigMarkup|null
     */
    public ?TwigMarkup $code;

    /**
     * @var number
     */
    public int $width;

    /**
     * @var number
     */
    public int $height;

    /**
     * @var number
     */
    public int|float $aspectRatio;

    /**
     * @var string
     */
    public string $authorName;

    /**
     * @var string URL
     */
    public string $authorUrl;

    /**
     * @var array of images
     */
    public ?array $providerIcons = null;

    /**
     * @var string|null URL
     */
    public ?string $providerIcon = null;

    /**
     * @var string
     */
    public string $providerName;

    /**
     * @var string URL
     */
    public string $providerUrl;

    /**
     * @var string|null
     */
    public ?string $publishedTime = null;

    /**
     * @var string|null
     */
    public ?string $license = null;

    /**
     * @return array
     */
    protected function defineRules(): array
    {
        return [
            [['title', 'url', 'type'], 'required'],
            [
                ['title', 'description', 'authorName', 'providerName', 'publishedTime', 'license'],
                StringValidator::class,
            ],
            [
                ['url', 'image', 'authorUrl', 'providerIcon', 'providerUrl'],
                UrlValidator::class,
                'defaultScheme' => 'https',
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
    public function jsonSerialize(): array
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
     * @return array|null
     */
    public function getImageToSize(int $size): ?array
    {
        return EmbeddedAssets::$plugin->methods->getImageToSize($this, $size);
    }

    /**
     * Method wrapper for Service::getProviderIconToSize
     *
     * @param int $size
     * @return array|null
     */
    public function getProviderIconToSize(int $size): ?array
    {
        return EmbeddedAssets::$plugin->methods->getProviderIconToSize($this, $size);
    }

    /**
     * Returns the iframe source URL with additional params passed.
     *
     * @since 2.6.0
     * @param array $params
     * @return string
     */
    public function getIframeSrc(array $params): string
    {
        if (!$this->_codeHasIframe()) {
            throw new Exception('The embedded asset code does not contain an iframe');
        }

        return $this->_getIframeSrc($params, true);
    }

    /**
     * Returns the iframe code with additional params passed to the source URL.
     *
     * @since 2.6.0
     * @param string[] $params Parameters to add to the iframe source URL, in the format `param` or `param=value`
     * @param string[] $attributes Attributes to add to the iframe element, in the format `attribute` or `attribute=value`
     * @param string[] $removeAttributes Attributes to remove from the iframe element
     * @return TwigMarkup
     */
    public function getIframeCode(array $params = [], array $attributes = [], array $removeAttributes = []): TwigMarkup
    {
        $newSrc = $this->getIframeSrc($params);
        $tagAttributes = ['src' => $newSrc];

        foreach ($attributes as $attribute) {
            $splitAttr = explode('=', $attribute, 2);

            // Ignore the `src` attribute
            if ($splitAttr[0] !== 'src') {
                $tagAttributes[$splitAttr[0]] = count($splitAttr) === 1 ? true : $splitAttr[1];
            }
        }

        foreach ($removeAttributes as $attribute) {
            // Ignore the `src` attribute
            if ($attribute !== 'src') {
                $tagAttributes[$attribute] = null;
            }
        }

        $code = HtmlHelper::modifyTagAttributes($this->code, $tagAttributes);

        return Template::raw($code);
    }

    /**
     * Returns the URL with additional params passed. Has to be type of video.
     *
     * @since 2.0.8
     * @param array $params
     * @return string|null
     */
    public function getVideoUrl(array $params): ?string
    {
        return $this->type === 'video' && is_array($params) ? $this->_getIframeSrc($params, false) : null;
    }

    /**
     * Returns the raw code with additional params passed. Has to be type of video.
     *
     * @since 2.0.8
     * @param array $params
     * @return TwigMarkup
     */
    public function getVideoCode(array $params): TwigMarkup
    {
        if ($this->type !== 'video') {
            throw new Exception('Tried to call getVideoCode() on an embedded asset with a type other than video');
        }

        $newSrc = $this->_getIframeSrc($params, false);
        $code = HtmlHelper::modifyTagAttributes($this->code, ['src' => $newSrc]);

        return Template::raw($code);
    }

    /**
     * Gets this embedded asset's video ID, if the embedded asset is from a supported provider.
     *
     * Providers supported by this method:
     * - Dailymotion
     * - Vimeo
     * - Wistia
     * - YouTube
     *
     * @since 2.2.3
     * @return string|null the video ID, or null if the embedded asset is not from a supported provider
     */
    public function getVideoId(): ?string
    {
        if ($this->type !== "video") {
            return null;
        }

        $url = explode('/', $this->getMatchedVideoUrl());

        return match ($this->providerName) {
            'YouTube', 'Vimeo' => explode('?', $url[4])[0],
            'Dailymotion' => explode('?', $url[5])[0],
            'Wistia, Inc.' => $url[5],
            default => null,
        };
    }

    /**
     * @param array $params
     * @param bool $overrideParams
     * @return string
     */
    private function _getIframeSrc(array $params, bool $overrideParams): string
    {
        return $this->_addParamsToUrl($params, HtmlHelper::parseTagAttributes($this->_codeIframe())['src'], $overrideParams);
    }

    /**
     * Returns the modified url with params added.
     *
     * @return string
     */
    private function _addParamsToUrl($newParams, $pUrl, $overrideParams): string
    {
        if ($overrideParams) {
            $startPos = strpos($pUrl, '?');
            $newUrl = $startPos ? substr($pUrl, 0, $startPos) : $pUrl;
            $oldParams = $startPos !== false ? explode('&', substr($pUrl, $startPos + 1)) : [];
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
     * Returns the first iframe in this embedded asset's code, if any.
     *
     * @return string|null
     */
    private function _codeIframe(): ?string
    {
        preg_match_all('/<iframe (.+)><\/iframe>/', $this->code, $matches);
        return !empty($matches[0]) ? $matches[0][0] : null;
    }

    /**
     * Returns whether this embedded asset's code contains an iframe.
     *
     * @return bool
     */
    private function _codeHasIframe(): bool
    {
        return (bool)$this->_codeIframe();
    }

    /**
     * Returns the embedded video URL.
     *
     * @return string
     */
    private function getMatchedVideoUrl(): string
    {
        preg_match('/src="([^"]+)"/', $this->code, $match);

        return $match[1];
    }
}
