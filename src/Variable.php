<?php

namespace spicyweb\embeddedassets;

use Craft;
use craft\elements\Asset;

use spicyweb\embeddedassets\Plugin as EmbeddedAssets;
use spicyweb\embeddedassets\models\EmbeddedAsset;

/**
 * Class Variable
 *
 * @package spicyweb\embeddedassets
 * @author Spicy Web <craft@spicyweb.com.au>
 * @author Benjamin Fleming
 * @since 1.0.0
 */
class Variable
{
    /**
     * Retrieves the embedded asset model from an asset, if one exists.
     *
     * @param Asset $asset
     * @return EmbeddedAsset|null
     */
    public function get(Asset $asset)
    {
        return EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);
    }
}
