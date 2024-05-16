<?php

namespace spicyweb\embeddedassets;

use Craft;
use craft\elements\Asset;
use craft\helpers\Assets;
use craft\helpers\FileHelper;
use craft\helpers\Html as HtmlHelper;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\helpers\UrlHelper;
use craft\models\VolumeFolder;
use DateTimeImmutable;
use DateTimeInterface;
use DOMDocument;
use Embed\Embed;
use Embed\Extractor;
use Embed\Http\Crawler;
use Embed\Http\CurlClient;
use Embed\Http\Url;
use spicyweb\embeddedassets\adapters\akamai\Extractor as AkamaiExtractor;
use spicyweb\embeddedassets\adapters\bluesky\Extractor as BlueskyExtractor;
use spicyweb\embeddedassets\adapters\default\Extractor as DefaultExtractor;
use spicyweb\embeddedassets\adapters\default\detectors\Type as TypeDetector;
use spicyweb\embeddedassets\adapters\googlemaps\Extractor as GoogleMapsExtractor;
use spicyweb\embeddedassets\adapters\openstreetmap\Extractor as OpenStreetMapExtractor;
use spicyweb\embeddedassets\adapters\pbs\Extractor as PbsExtractor;
use spicyweb\embeddedassets\adapters\sharepoint\Extractor as SharepointExtractor;
use spicyweb\embeddedassets\errors\NotWhitelistedException;
use spicyweb\embeddedassets\errors\RefreshException;
use spicyweb\embeddedassets\events\BeforeRequestEvent;
use spicyweb\embeddedassets\jobs\InstagramRefreshCheck;
use spicyweb\embeddedassets\models\EmbeddedAsset;
use spicyweb\embeddedassets\Plugin as EmbeddedAssets;
use Twig\Markup as TwigMarkup;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidArgumentException;

/**
 * Class Service
 *
 * @package spicyweb\embeddedassets
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @author Benjamin Fleming
 * @since 1.0.0
 */
class Service extends Component
{
    /**
     * @event BeforeRequestEvent The event that is triggered before a request is made.
     * @since 4.0.0
     */
    public const EVENT_BEFORE_REQUEST = 'beforeRequest';

    private array $embeddedAssetData = [];

    /**
     * Requests embed data from a URL.
     *
     * @param string $url
     * @param bool $checkCache Whether to check for data associated with the URL that's been stored in Craft's cache
     * @return EmbeddedAsset
     * @throws NotWhitelistedException if the `preventNonWhitelistedUploads` setting is enabled and the embedded asset
     * provider is non-whitelisted.
     */
    public function requestUrl(string $url, bool $checkCache = true): EmbeddedAsset
    {
        $pluginSettings = EmbeddedAssets::$plugin->getSettings();
        $cacheService = Craft::$app->getCache();
        $cacheKey = 'embeddedasset:' . $url;
        $embeddedAsset = $checkCache ? $cacheService->get($cacheKey) : null;

        if (!$embeddedAsset) {
            $pluginSettings = EmbeddedAssets::$plugin->getSettings();
            $array = $this->_getDataFromEmbed($url);
            $embeddedAsset = $this->createEmbeddedAsset($array);

            $cacheService->set($cacheKey, $embeddedAsset, $pluginSettings->cacheDuration);
        }

        if ($pluginSettings->preventNonWhitelistedUploads && !$embeddedAsset->getIsSafe()) {
            throw new NotWhitelistedException(
                Craft::t('embeddedassets', 'Tried to upload embedded asset with non-whitelisted provider'),
                $embeddedAsset,
            );
        }

        return $embeddedAsset;
    }

    private function _getDataFromEmbed(string $url): array
    {
        $pluginSettings = EmbeddedAssets::$plugin->getSettings();
        $embedSettings = [
            'oembed:query_parameters' => [],
        ];

        if (!empty($pluginSettings->parameters)) {
            foreach ($pluginSettings->parameters as $parameter) {
                $param = $parameter['param'];
                $value = $parameter['value'];
                $embedSettings['oembed:query_parameters'][$param] = $value;
            }
        }

        if ($pluginSettings->googleKey) {
            $embedSettings['google:key'] = Craft::parseEnv($pluginSettings->googleKey);
        }
                if ($pluginSettings->facebookKey) {
            $embedSettings['facebook:token'] = $embedSettings['instagram:token'] = Craft::parseEnv($pluginSettings->facebookKey);
        }

        $clientSettings = [];
        $adapters = [
            'akamaized.net' => AkamaiExtractor::class,
            'bsky.app' => BlueskyExtractor::class,
            'bsky.social' => BlueskyExtractor::class,
            'pbs.org' => PbsExtractor::class,
            'nhpbs.org' => PbsExtractor::class,
            'openstreetmap.org' => OpenStreetMapExtractor::class,
            'sharepoint.com' => SharepointExtractor::class,
        ];

        if (
            preg_match('/:\/\/maps.google.([a-z\.]+)\/?/', $url, $matches) ||
            preg_match('/:\/\/www.google.([a-z\.]+)\/maps\/?/', $url, $matches)
        ) {
            $adapters['google.' . $matches[1]] = GoogleMapsExtractor::class;
        }

        $headers = array_filter([
            'Referer' => $pluginSettings->referer ? Craft::parseEnv($pluginSettings->referer) : null,
        ]);

        // Allow other plugins/modules to modify the settings
        if ($this->hasEventHandlers(self::EVENT_BEFORE_REQUEST)) {
            $event = new BeforeRequestEvent(compact(
                'adapters',
                'clientSettings',
                'embedSettings',
                'headers',
                'url',
            ));
            $this->trigger(self::EVENT_BEFORE_REQUEST, $event);
            $adapters = $event->adapters;
            $clientSettings = $event->clientSettings;
            $embedSettings = $event->embedSettings;
            $headers = $event->headers;
        }

        // Create the Embed object, set the adapters and settings
        $client = new CurlClient();
        $client->setSettings($clientSettings);
        $crawler = new Crawler($client);
        $crawler->addDefaultHeaders($headers);
        $embed = new Embed($crawler);
        $factory = $embed->getExtractorFactory();

        foreach ($adapters as $host => $class) {
            $factory->addAdapter($host, $class);
        }

        $factory->setDefault(DefaultExtractor::class);
        $factory->addDetector('type', TypeDetector::class);
        $embed->setSettings($embedSettings);

        // Now get the embed data
        $embedData = $embed->get($url);

        return $this->_convertFromExtractor($embedData);
    }

    /**
     * Checks a URL against the whitelist set on the plugin settings model.
     *
     * @param string $url
     * @return bool Whether the URL is in the whitelist.
     */
    public function checkWhitelist(string $url): bool
    {
        $pluginSettings = EmbeddedAssets::$plugin->getSettings();
        $whitelist = array_merge($pluginSettings->whitelist, $pluginSettings->extraWhitelist);

        foreach ($whitelist as $whitelistUrl) {
            if ($whitelistUrl) {
                $pattern = explode('*', $whitelistUrl);
                $pattern = array_map('preg_quote', $pattern);
                $pattern = implode('[a-z][a-z0-9]*', $pattern);
                $pattern = "%^(https?:)?//([a-z0-9\-]+\\.)?$pattern([:/].*)?$%";

                if (preg_match($pattern, $url)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retrieves an embedded asset model from an asset element, if one exists.
     *
     * @param Asset $asset
     * @return EmbeddedAsset|null
     * @throws \yii\base\InvalidConfigException
     * @throws \craft\errors\AssetException
     */
    public function getEmbeddedAsset(?Asset $asset): ?EmbeddedAsset
    {
        // Embedded assets are just JSON files, so clearly if this isn't a JSON file it can't be an embedded asset
        if ($asset?->kind !== Asset::KIND_JSON) {
            return null;
        }

        // If the embedded asset data has already been loaded this request, there's no need to reload it
        if (isset($this->embeddedAssetData[$asset->uid])) {
            return $this->embeddedAssetData[$asset->uid];
        }

        $pluginSettings = EmbeddedAssets::$plugin->getSettings();
        $embeddedAsset = null;

        try {
            $decodedJson = $this->_getAssetContents($asset);

            // Automatic refreshing of Instagram embedded assets every seven days, see issue #114 for why
            if (
                $pluginSettings->enableAutoRefresh
                && strtolower($decodedJson['providerName']) === 'instagram'
                && $this->_hasBeenWeekSince($asset->dateModified)
            ) {
                Craft::$app->queue->push(new InstagramRefreshCheck([
                    'asset' => $asset,
                    'embeddedAssetData' => $decodedJson,
                ]));
            }

            // Make YouTube iframes use the nocookie embed URL if the relevant setting is enabled
            if ($decodedJson['providerName'] === 'YouTube' && $pluginSettings->useYouTubeNoCookie) {
                $decodedJson['code'] = preg_replace(
                    '/src="https?:\/\/www.youtube.com\/embed\/(.+)\?/',
                    'src="https://www.youtube-nocookie.com/embed/$1?',
                    $decodedJson['code']
                );
            }

            // Make Vimeo iframes use the nocookie embed URL if the relevant setting is enabled
            if ($decodedJson['providerName'] === 'Vimeo' && $pluginSettings->disableVimeoTracking) {
                $oldSrc = HtmlHelper::parseTagAttributes($decodedJson['code'])['src'];
                $newSrc = HtmlHelper::decode(UrlHelper::urlWithParams($oldSrc, ['dnt' => '1']));
                $decodedJson['code'] = HtmlHelper::modifyTagAttributes($decodedJson['code'], ['src' => $newSrc]);
            }

            if (is_array($decodedJson)) {
                $embeddedAsset = $this->createEmbeddedAsset($decodedJson);
                $this->embeddedAssetData[$asset->uid] = $embeddedAsset;
            }
        } catch (\Throwable $e) {
            // Ignore errors and assume it's not an embedded asset
            $embeddedAsset = null;
        }

        return $embeddedAsset;
    }

    /**
     * Returns whether the given array represents valid data for creating an EmbeddedAsset.
     *
     * @since 2.3.0
     * @param array $array
     * @return bool
     */
    public function isValidEmbeddedAssetData(array $array): bool
    {
        return $this->createEmbeddedAsset($array) !== null;
    }

    /**
     * Creates an embedded asset model from an array of property/value pairs.
     * Returns the model unless the array included any properties that aren't defined on the model.
     *
     * @param array $array
     * @return EmbeddedAsset|null
     */
    public function createEmbeddedAsset(array $array): ?EmbeddedAsset
    {
        $isLegacy = isset($array['__embeddedasset__']);
        if ($isLegacy) {
            $array = $this->_convertFromLegacy($array);
        }

        foreach (array_keys($array) as $key) {
            if (
                !property_exists(EmbeddedAsset::class, $key) &&
                !in_array($key, EmbeddedAsset::deprecatedProperties())
            ) {
                return null;
            }
        }

        if (isset($array['code'])) {
            $code = $array['code'] instanceof TwigMarkup ? (string)$array['code'] : (is_string($array['code']) ? $array['code'] : '');
            $array['code'] = empty($code) ? null : Template::raw($code);
        }

        // Attempts to extract missing dimensional properties from the embed code
        $dimensions = $this->_getDimensions($array);
        $array['width'] = $dimensions[0] ?: null;
        $array['height'] = $dimensions[1] ?: null;

        // Sets aspect ratio if it's missing
        if (!isset($array['aspectRatio']) && isset($array['width']) && isset($array['height'])) {
            $array['aspectRatio'] = $array['height'] / $array['width'] * 100;
        }

        // Correct invalid types to similar, valid types
        if (isset($array['type']) && $array['type'] === 'photo') {
            $array['type'] = 'image';
        }

        $embeddedAsset = new EmbeddedAsset($array);

        return $embeddedAsset->validate() ? $embeddedAsset : null;
    }

    /**
     * Refreshes an embedded asset with the current data from the embedded asset's URL.
     *
     * @param Asset $asset
     * @param EmbeddedAsset|null $embeddedAsset
     * @return EmbeddedAsset the refreshed embedded asset
     * @throws RefreshException if the embedded asset could not be refreshed
     * @since 3.0.2
     */
    public function refreshEmbeddedAsset(Asset $asset, ?EmbeddedAsset $embeddedAsset = null): EmbeddedAsset
    {
        if ($embeddedAsset === null) {
            $embeddedAsset = $this->getEmbeddedAsset($asset) ?? throw new RefreshException('Asset is not an embedded asset');
        }

        $assetsService = Craft::$app->getAssets();
        $elementsService = Craft::$app->getElements();
        $folder = $asset->getFolder();
        $newEmbeddedAsset = $this->requestUrl($embeddedAsset->url, false);

        // Retain deprecated property values not supported by Embed 4
        foreach (['imageHeight', 'imageWidth', 'images', 'providerIcons', 'tags', 'type'] as $prop) {
            $newEmbeddedAsset->{$prop} = $embeddedAsset->{$prop};
        }

        $newAsset = $this->createAsset($newEmbeddedAsset, $folder);
        $result = $elementsService->saveElement($newAsset);

        if (!$result) {
            throw new RefreshException(implode('; ', $newAsset->getFirstErrors()));
        }

        $tempPath = $newAsset->getCopyOfFile();
        $assetsService->replaceAssetFile($asset, $tempPath, $asset->filename);
        $elementsService->deleteElement($newAsset);

        // Replace the old cached data for the embedded asset
        Craft::$app->getCache()->set(
            $this->getCachedAssetKey($asset),
            Json::encode($newEmbeddedAsset->jsonSerialize(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        return $newEmbeddedAsset;
    }

    /**
     * Creates an asset element ready to be saved from an embedded asset model.
     *
     * @param EmbeddedAsset $embeddedAsset
     * @param VolumeFolder $folder The folder to save the asset to.
     * @return Asset
     * @throws \craft\errors\AssetLogicException
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public function createAsset(EmbeddedAsset $embeddedAsset, VolumeFolder $folder): Asset
    {
        $assetsService = Craft::$app->getAssets();
        $pluginSettings = EmbeddedAssets::$plugin->getSettings();

        $tempFilePath = Assets::tempFilePath();
        $fileContents = Json::encode($embeddedAsset,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        FileHelper::writeToFile($tempFilePath, $fileContents);

        // Ensure the title contains no emoji
        $assetTitle = StringHelper::replaceMb4($embeddedAsset->title ?: $embeddedAsset->url, '');

        $fileName = Assets::prepareAssetName($assetTitle, false);
        $fileName = str_replace('.', '', $fileName);
        $fileName = $fileName ?: 'embedded-asset';
        $fileName = StringHelper::safeTruncate($fileName, $pluginSettings->maxFileNameLength) . '.json';
        $fileName = $assetsService->getNameReplacementInFolder($fileName, $folder->id);

        $asset = new Asset();
        $asset->title = StringHelper::safeTruncate($assetTitle, $pluginSettings->maxAssetNameLength);
        $asset->tempFilePath = $tempFilePath;
        $asset->filename = $fileName;
        $asset->newFolderId = $folder->id;
        $asset->volumeId = $folder->volumeId;
        $asset->uploaderId = Craft::$app->getUser()->getId();
        $asset->avoidFilenameConflicts = true;
        $asset->setScenario(Asset::SCENARIO_CREATE);

        return $asset;
    }

    /**
     * Checks an embedded asset embed code for URL's that are safe (or in other words, are whitelisted).
     *
     * @param EmbeddedAsset $embeddedAsset
     * @return bool
     */
    public function isEmbedSafe(EmbeddedAsset $embeddedAsset): bool
    {
        $isSafe = $this->checkWhitelist($embeddedAsset->url);

        if ($isSafe) {
            $dom = $this->getEmbedCode($embeddedAsset);

            if ($dom) {
                foreach ($dom->getElementsByTagName('iframe') as $iframeElement) {
                    $href = $iframeElement->getAttribute('href');
                    $isSafe = $isSafe && (!$href || $this->checkWhitelist($href));
                }

                foreach ($dom->getElementsByTagName('script') as $scriptElement) {
                    $src = $scriptElement->getAttribute('src');
                    $content = $scriptElement->textContent;

                    // Inline scripts are impossible to analyse for safety, so just assume they're all evil
                    $isSafe = $isSafe && !$content && (!$src || $this->checkWhitelist($src));
                }
            }
        }

        return $isSafe;
    }

    /**
     * Gets the embed code as DOM, if one exists and is valid.
     *
     * @param EmbeddedAsset $embeddedAsset
     * @return DOMDocument|null
     */
    public function getEmbedCode(EmbeddedAsset $embeddedAsset): ?DOMDocument
    {
        $dom = null;

        if ($embeddedAsset->code) {
            $errors = libxml_use_internal_errors(true);
            $entities = libxml_disable_entity_loader(true);

            try {
                $dom = new DOMDocument();
                $code = "<div>$embeddedAsset->code</div>";
                $isHtml = $dom->loadHTML($code, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

                if (!$isHtml) {
                    throw new ErrorException();
                }
            } catch (ErrorException $e) {
                // Corrupted code property, likely due to invalid HTML.
                $dom = null;
            } finally {
                libxml_use_internal_errors($errors);
                libxml_disable_entity_loader($entities);
            }
        }

        return $dom;
    }

    /**
     * Gets the HTML for the embedded asset.
     * This method automatically checks if the embed code is safe to use. If it is, then the embed code is returned.
     * Otherwise, if the embedded asset is not a "link" type and it has an image, an <img> tag is returned. Otherwise,
     * an <a> link tag is returned.
     *
     * @param EmbeddedAsset $embeddedAsset
     * @return TwigMarkup
     */
    public function getEmbedHtml(EmbeddedAsset $embeddedAsset): TwigMarkup
    {
        if ($embeddedAsset->code && $embeddedAsset->getIsSafe()) {
            $html = $embeddedAsset->code;
        } elseif ($embeddedAsset->type !== 'link' && $embeddedAsset->image) {
            $html = Template::raw("<img src=\"$embeddedAsset->image\" alt=\"$embeddedAsset->title\" width=\"$embeddedAsset->imageWidth\" height=\"$embeddedAsset->imageHeight\">");
        } else {
            $html = Template::raw("<a href=\"$embeddedAsset->url\" target=\"_blank\" rel=\"noopener\">$embeddedAsset->title</a>");
        }

        return $html;
    }

    /**
     * Returns the image from an embedded asset closest to some size.
     * It favours images that most minimally exceed the supplied size.
     *
     * @param EmbeddedAsset $embeddedAsset
     * @param int $size
     * @return array|null
     */
    public function getImageToSize(EmbeddedAsset $embeddedAsset, int $size): ?array
    {
        return is_array($embeddedAsset->images) ?
            $this->_getImageToSize($embeddedAsset->images, $size) : null;
    }

    /**
     * Returns the provider icon from an embedded asset closest to some size.
     * It favours icons that most minimally exceed the supplied size.
     *
     * @param EmbeddedAsset $embeddedAsset
     * @param int $size
     * @return array|null
     */
    public function getProviderIconToSize(EmbeddedAsset $embeddedAsset, int $size): ?array
    {
        return is_array($embeddedAsset->providerIcons) ?
            $this->_getImageToSize($embeddedAsset->providerIcons, $size) : null;
    }

    /**
     * Gets the width/height of an embedded asset.
     * Attempts to extract missing dimensional properties from the embed code.
     *
     * @param array $array
     * @return array
     */
    private function _getDimensions(array $array): array
    {
        $embeddedAsset = new EmbeddedAsset($array);
        $width = $embeddedAsset->width;
        $height = $embeddedAsset->height;

        if (!$width || !$height) {
            $dom = $this->getEmbedCode($embeddedAsset);

            if ($dom) {
                $iframeElement = $dom->getElementsByTagName('iframe')->item(0);

                if ($iframeElement) {
                    $width = $iframeElement->getAttribute('width');
                    $height = $iframeElement->getAttribute('height');
                    $style = $iframeElement->getAttribute('style');

                    $matches = [];
                    $matchCount = preg_match_all('/(width|height):\s*([0-9]+(\.[0-9]+)?)px/i', $style, $matches);

                    for ($i = 0; $i < $matchCount; $i++) {
                        $styleProperty = strtolower($matches[1][$i]);
                        $styleValue = $matches[2][$i];

                        switch ($styleProperty) {
                            case 'width':
                                $width = $styleValue;
                                break;
                            case 'height':
                                $height = $styleValue;
                                break;
                        }
                    }

                    $width = $width ? floatval($width) : null;
                    $height = $height ? floatval($height) : null;
                }
            }
        }

        // TikTok sets the width and height as '100%', which Embedded Assets doesn't really like.  Just remove them.
        if ($width === '100%' && $height === '100%') {
            $width = null;
            $height = null;
        }

        return [$width, $height];
    }

    /**
     * Helper method for retrieving an image closest to some size.
     *
     * @param array $images
     * @param int $size
     * @return array|null
     */
    private function _getImageToSize(array $images, int $size): ?array
    {
        $selectedImage = null;
        $selectedSize = 0;

        foreach ($images as $image) {
            if (is_array($image)) {
                $imageWidth = isset($image['width']) && is_numeric($image['width']) ? $image['width'] : 0;
                $imageHeight = isset($image['height']) && is_numeric($image['height']) ? $image['height'] : 0;
                $imageSize = max($imageWidth, $imageHeight);

                if (
                    !$selectedImage ||
                    ($selectedSize < $size && $imageSize > $selectedSize) ||
                    ($selectedSize > $imageSize && $imageSize > $size)
                ) {
                    $selectedImage = $image;
                    $selectedSize = $imageSize;
                }
            }
        }

        return $selectedImage;
    }

    /**
     * Creates an "embedded asset ready" array from an Extractor.
     *
     * @param Extractor $extractor
     * @return array
     */
    private function _convertFromExtractor(Extractor $extractor): array
    {
        return [
            'title' => $extractor->title,
            'description' => $extractor->description,
            'url' => (string)$extractor->url,
            'type' => $extractor->type,
            'image' => (string)$extractor->image,
            'code' => Template::raw($extractor->code?->html ?: ''),
            'width' => $extractor->code?->width,
            'height' => $extractor->code?->height,
            'aspectRatio' => $extractor->code?->ratio,
            'authorName' => $extractor->authorName,
            'authorUrl' => (string)$extractor->authorUrl,
            'providerName' => $extractor->providerName,
            'providerUrl' => (string)$extractor->providerUrl,
            'providerIcon' => (string)$extractor->icon,
            'publishedTime' => $extractor->publishedTime?->format('Y-m-d'),
            'license' => $extractor->license,
            'feeds' => array_map(function ($feed) { return (string)$feed; }, $extractor->feeds),

            // New properties in Embed 4
            'cms' => $extractor->cms,
            'favicon' => (string)$extractor->favicon,
            'keywords' => $extractor->keywords,
            'language' => $extractor->language,
            'languages' => array_map(function ($language) { return (string)$language; }, $extractor->languages),
            'redirect' => (string)$extractor->redirect,
        ];
    }

    /**
     * Creates an "embedded asset ready" array from an array matching the Craft 2 version of the plugin.
     *
     * @param array $legacy
     * @return array
     */
    private function _convertFromLegacy(array $legacy): array
    {
        $width = intval($legacy['width'] ?? 0);
        $height = intval($legacy['height'] ?? 0);
        $imageUrl = $legacy['thumbnailUrl'] ?? null;
        $imageWidth = intval($legacy['thumbnailWidth'] ?? 0);
        $imageHeight = intval($legacy['thumbnailHeight'] ?? 0);

        return [
            'title' => $legacy['title'] ?? null,
            'description' => $legacy['description'] ?? null,
            'url' => $legacy['url'] ?? null,
            'type' => $legacy['type'] ?? null,
            'images' => $imageUrl ? [
                [
                    'url' => $imageUrl,
                    'width' => $imageWidth,
                    'height' => $imageHeight,
                    'size' => $imageWidth * $imageHeight,
                    'mime' => null,
                ],
            ] : [],
            'image' => $imageUrl,
            'imageWidth' => $imageWidth,
            'imageHeight' => $imageHeight,
            'code' => Template::raw($legacy['html'] ?? $legacy['safeHtml'] ?? ''),
            'width' => $width,
            'height' => $height,
            'aspectRatio' => $width > 0 ? $height / $width * 100 : 0,
            'authorName' => $legacy['authorName'] ?? null,
            'authorUrl' => $legacy['authorUrl'] ?? null,
            'providerName' => $legacy['providerName'] ?? null,
            'providerUrl' => $legacy['providerUrl'] ?? null,
        ];
    }

    /**
     * Gets the contents of the given asset.
     *
     * If a cached copy of the embedded asset exists, it will be loaded.  If a cached copy does not exist, one will be
     * created after loading the embedded asset from its original location.
     *
     * @param Asset $asset
     * @return array of embedded asset data
     */
    private function _getAssetContents(Asset $asset): array
    {
        $contents = Craft::$app->getCache()->getOrSet(
            $this->getCachedAssetKey($asset),
            static function() use ($asset) {
                return $asset->getContents();
            },
            0);

        try {
            $contents = Json::decode($contents);
        } catch (\Throwable $e) {
            throw new Exception('Tried to get the contents of a non-embedded asset');
        }

        return $contents;
    }

    /**
     * Gets the cached key for the given asset.
     *
     * @since 2.5.0
     * @param Asset $asset
     * @return string the embedded asset's cached key
     * @throws InvalidArgumentException if $asset is an unsaved Asset
     */
    public function getCachedAssetKey(Asset $asset): string
    {
        if ($asset->uid === null) {
            throw new InvalidArgumentException('Tried to get the cached key of an unsaved embedded asset');
        }

        return 'embeddedassets:' . $asset->uid;
    }

    private function _hasBeenWeekSince(DateTimeInterface $dateModified): bool
    {
        return $dateModified->diff(new DateTimeImmutable())->d >= 7;
    }
}
