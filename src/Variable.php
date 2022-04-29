<?php

namespace spicyweb\embeddedassets;

use Craft;
use craft\elements\Asset;

use spicyweb\embeddedassets\models\EmbeddedAsset;
use spicyweb\embeddedassets\Plugin as EmbeddedAssets;

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
    public function get(Asset $asset): ?EmbeddedAsset
    {
        return EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);
    }

    /**
     * Creates an embedded asset model from an array of property/value pairs.
     * Returns the model unless the array included any properties that aren't defined on the model.
     *
     * @param array $array
     * @return EmbeddedAsset|null
     * @since 2.10.0
     */
    public function create(array $array): ?EmbeddedAsset
    {
        return EmbeddedAssets::$plugin->methods->createEmbeddedAsset($array);
    }
}
