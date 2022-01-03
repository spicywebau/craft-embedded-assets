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

    /**
     * Creates an embedded asset model from an array of property/value pairs.
     * Returns the model unless the array included any properties that aren't defined on the model.
     *
     * @param array $array
     * @return EmbeddedAsset|null
     * @since 2.10.0
     */
    public function create(array $array)
    {
        return EmbeddedAssets::$plugin->methods->createEmbeddedAsset($array);
    }

    //
    // Deprecated methods
    
    /**
     * Determines if an asset is an embedded asset or not.
     *
     * @param Asset $asset
     * @throws
     * @return bool
     * @deprecated Will be removed in next major version. Use the `get` method instead.
     *
     */
    public function isEmbedded(Asset $asset): bool
    {
        Craft::$app->getDeprecator()->log('craft.embeddedAssets.isEmbedded',
            "The template method `craft.embeddedAssets.isEmbedded` is now deprecated. Use `craft.embeddedAssets.get` instead.");
        
        return (bool)EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);
    }
    
    /**
     * Retrieves the embedded asset model from an asset, if one exists.
     *
     * @param Asset $asset
     * @throws
     * @return mixed
     * @deprecated Will be removed in next major version. Use the `get` method instead.
     *
     */
    public function fromAsset(Asset $asset)
    {
        Craft::$app->getDeprecator()->log('craft.embeddedAssets.fromAsset',
            "The template method `craft.embeddedAssets.fromAsset` is now deprecated. Use `craft.embeddedAssets.get` instead.");
        
        return EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);
    }
    
    /**
     * Retrieves the embedded asset models from an array of assets.
     *
     * @param mixed $assets An iterable object of asset models.
     * @param null $indexBy Whether to index the resulting array by a property of the asset.
     * @throws
     * @return array
     * @deprecated Will be removed in next major version. Use the `get` method instead.
     *
     */
    public function fromAssets($assets, $indexBy = null): array
    {
        Craft::$app->getDeprecator()->log('craft.embeddedAssets.fromAssets',
            "The template method `craft.embeddedAssets.fromAssets` is now deprecated. Use `craft.embeddedAssets.get` instead.");
        
        $embeddedAssets = [];
        
        if (is_iterable($assets)) {
            foreach ($assets as $asset) {
                $embeddedAsset = $asset instanceof Asset ?
                    EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset) : null;
                
                if ($embeddedAsset) {
                    if (is_string($indexBy) && $asset->hasProperty($indexBy)) {
                        $embeddedAssets[$asset->$indexBy] = $embeddedAsset;
                    } else {
                        $embeddedAssets[] = $embeddedAsset;
                    }
                }
            }
        }
        
        return $embeddedAssets;
    }
}
