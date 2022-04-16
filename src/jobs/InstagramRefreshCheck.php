<?php

namespace spicyweb\embeddedassets\jobs;

use Craft;
use craft\helpers\Json;
use craft\queue\BaseJob;
use craft\elements\Asset;
use spicyweb\embeddedassets\Plugin as EmbeddedAssets;

/**
 * A job to check if an Instagram embedded asset needs to be auto-refreshed.
 *
 * @package spicyweb\embeddedassets\jobs
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.10.4
 */
class InstagramRefreshCheck extends BaseJob
{
    /**
     * @var Asset
     */
    public $asset;

    /**
     * An array of embedded asset data. If `null`, the asset's contents will be used instead.
     *
     * @var array|null
     */
    public $embeddedAssetData;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        // Check if the data was sent through, and if not, get it from the asset contents
        if ($this->embeddedAssetData === null) {
            $this->embeddedAssetData = Json::decode($asset->getContents());
        }

        $hasImageExpired = $this->_hasInstagramImageExpired($this->embeddedAssetData['image']);
        $this->setProgress($queue, 0.5);

        if ($hasImageExpired) {
            $this->_updateInstagramFile($this->asset, $this->embeddedAssetData['url']);
        } else {
            // If it hasn't expired yet, update the date modified so it checks in another seven days
            $this->asset->dateModified = new \DateTime();
            Craft::$app->getElements()->saveElement($this->asset);
        }

        $this->setProgress($queue, 1);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('embeddedassets', 'Checking whether Instagram embedded asset needs to be refreshed');
    }

    /**
     * Checks whether an Instagram image URL has expired.
     *
     * @param string|null $imageUrl
     * @return bool
     */
    private function _hasInstagramImageExpired(?string $imageUrl = null): bool
    {
        // If we didn't get an image URL, then... yes?
        if ($imageUrl === null) {
            return true;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);

        $output = curl_exec($ch);
        curl_close($ch);

        $output = rtrim($output);
        $data = explode("\n", $output);

        return $data && strpos($data[0], '200') === false;
    }

    /**
     * Refreshes an Instagram embedded asset with data from the given URL.
     *
     * @param Asset $asset
     * @param string $url
     */
    private function _updateInstagramFile(Asset $asset, $url)
    {
        // Fix URL in case we got a login URL and not an Instagram URL referring to a post
        // We add the post ID at the end
        if(strpos($url, 'login') !== false) {
            parse_str(parse_url($url)['query'], $params);
            $url = "https://www.instagram.com" . $params['next'];
        }

        // Get new data from the URL
        $newEmbeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($url, false);

        if ($newEmbeddedAsset) {
            try {
                $assetsService = Craft::$app->getAssets();
                $elementsService = Craft::$app->getElements();

                $folder = $assetsService->findFolder(['id' => $asset->folderId]);
                $assetToReplace = EmbeddedAssets::$plugin->methods->createAsset($newEmbeddedAsset, $folder);
                $elementsService->saveElement($assetToReplace);

                $tempPath = $assetToReplace->getCopyOfFile();
                $assetsService->replaceAssetFile($asset, $tempPath, $asset->filename);
                $elementsService->deleteElement($assetToReplace);

                // Replace the old cached data for the embedded asset
                Craft::$app->getCache()->set(
                    EmbeddedAssets::$plugin->methods->getCachedAssetKey($asset),
                    Json::encode($newEmbeddedAsset->jsonSerialize(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                );
            } catch (\Throwable $e) {
                Craft::warning("Couldn't refresh Instagram embedded asset with asset ID '{$asset->id}': " . $e->getMessage());
            }
        }
    }
}
