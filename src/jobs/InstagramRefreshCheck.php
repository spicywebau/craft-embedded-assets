<?php

namespace spicyweb\embeddedassets\jobs;

use Craft;
use craft\elements\Asset;
use craft\queue\BaseJob;
use spicyweb\embeddedassets\models\EmbeddedAsset;
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
    public Asset $asset;

    /**
     * An array of embedded asset data. If `null`, the asset's contents will be used instead.
     *
     * @var array|null
     */
    public ?array $embeddedAssetData;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        // Check if the data was sent through, and if not, get it from the asset contents
        if ($this->embeddedAssetData === null) {
            $embeddedAsset = EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);
        } else {
            $embeddedAsset = EmbeddedAssets::$plugin->methods->createEmbeddedAsset($this->embeddedAssetData);
        }

        $hasImageExpired = $this->_hasInstagramImageExpired($embeddedAsset->image);
        $this->setProgress($queue, 0.5);

        if ($hasImageExpired) {
            $this->_updateInstagramFile($this->asset, $embeddedAsset);
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
    protected function defaultDescription(): ?string
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
     * @param EmbeddedAsset $embeddedAsset
     */
    private function _updateInstagramFile(Asset $asset, EmbeddedAsset $embeddedAsset)
    {
        // Fix URL in case we got a login URL and not an Instagram URL referring to a post
        // We add the post ID at the end
        if (strpos($embeddedAsset->url, 'login') !== false) {
            parse_str(parse_url($embeddedAsset->url)['query'], $params);
            $embeddedAsset->url = "https://www.instagram.com" . $params['next'];
        }

        try {
            EmbeddedAssets::$plugin->methods->refreshEmbeddedAsset($asset, $embeddedAsset);
        } catch (\Throwable $e) {
            Craft::warning("Couldn't refresh Instagram embedded asset with asset ID '{$asset->id}': " . $e->getMessage());
        }
    }
}
