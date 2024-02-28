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
     * @var array of URLs
     */
    public ?array $feeds = null;

    /**
     * @var string URL
     */
    public string $image;

    /**
     * @var TwigMarkup
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
     * @var string URL
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
     * @var string
     */
    public $cms;

    /**
     * @var string
     */
    public $favicon;

    /**
     * @var array of strings
     */
    public $keywords;

    /**
     * @var string
     */
    public $language;

    /**
     * @var array of strings
     */
    public $languages;

    /**
     * @var string
     */
    public $redirect;

    // Deprecated properties (removed from Embed 4)

    /**
     * @var string link|image|video\rich
     * @deprecated in 4.0.0
     */
    public ?string $type = null;

    /**
     * @var array of images
     * @deprecated in 4.0.0
     */
    public ?array $images = null;

    /**
     * @var number
     * @deprecated in 4.0.0
     */
    public ?int $imageWidth = null;

    /**
     * @var number
     * @deprecated in 4.0.0
     */
    public ?int $imageHeight = null;

    /**
     * @var array of images
     * @deprecated in 4.0.0
     */
    public ?array $providerIcons = null;

    /**
     * @var array of strings
     * @deprecated in 4.0.0
     */
    public ?array $tags = null;

    private static array $_deprecatedProperties = [
        'imageHeight' => [
            'key' => 'EmbeddedAsset::imageHeight',
            'message' => 'The `imageHeight` embedded asset property has been deprecated, due to being removed in Embed 4.',
        ],
        'imageWidth' => [
            'key' => 'EmbeddedAsset::imageWidth',
            'message' => 'The `imageWidth` embedded asset property has been deprecated, due to being removed in Embed 4.',
        ],
        'images' => [
            'key' => 'EmbeddedAsset::images',
            'message' => 'The `images` embedded asset property has been deprecated, due to being removed in Embed 4. Use `image` instead.',
        ],
        'providerIcons' => [
            'key' => 'EmbeddedAsset::providerIcons',
            'message' => 'The `providerIcons` embedded asset property has been deprecated, due to being removed in Embed 4. Use `providerIcon` instead.',
        ],
        'tags' => [
            'key' => 'EmbeddedAsset::tags',
            'message' => 'The `tags` embedded asset property has been deprecated, due to being removed in Embed 4. Use `keywords` instead.',
        ],
    ];

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        $deprecator = Craft::$app->getDeprecator();

        // Deprecated image array properties
        foreach (['images', 'providerIcons'] as $prop) {
            if (isset($config[$prop])) {
                $deprecator->log(
                    static::$_deprecatedProperties[$prop]['key'],
                    static::$_deprecatedProperties[$prop]['message'],
                );
                $config[$prop] = array_map(
                    fn($image) => is_array($image) ? $image['url'] : $image,
                    $config[$prop],
                );
            }
        }

        if (isset($config['tags'])) {
            $deprecator->log(
                static::$_deprecatedProperties['tags']['key'],
                static::$_deprecatedProperties['tags']['message'],
            );
            $config['keywords'] = $config['tags'];
        }

        // Deprecated properties for which there is no re-setting of data
        foreach (['imageHeight', 'imageWidth'] as $prop) {
            if (isset($config[$prop])) {
                $deprecator->log(
                    static::$_deprecatedProperties[$prop]['key'],
                    static::$_deprecatedProperties[$prop]['message'],
                );
            }
        }

        parent::__construct($config);
    }

    /**
     * @return array
     */
    protected function defineRules(): array
    {
        return [
            [['title', 'url'], 'required'],
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
            [['keywords', 'images', 'providerIcons', 'tags'], 'each', 'rule' => [StringValidator::class]],
            [['feeds'], 'each', 'rule' => [UrlValidator::class]],
            [['width', 'height', 'aspectRatio', 'imageWidth', 'imageHeight'], 'number', 'min' => 0],
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
