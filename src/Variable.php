<?php
namespace benf\embeddedassets;

use Craft;
use craft\elements\Asset;

use benf\embeddedassets\Plugin as EmbeddedAssets;
use benf\embeddedassets\models\EmbeddedAsset;

/**
 * Class Variable
 * @package benf\embeddedassets
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
	 * Determines if an asset is an embedded asset or not.
	 *
	 * @deprecated Will be removed in next major version. Use the `get` method instead.
	 *
	 * @param Asset $asset
	 * @return bool
	 */
	public function isEmbedded(Asset $asset): bool
	{
		Craft::$app->getDeprecator()->log('craft.embeddedAssets.isEmbedded', "The template method `craft.embeddedAssets.isEmbedded` is now deprecated. Use `craft.embeddedAssets.get` instead.");

		return (bool)EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);
	}

	/**
	 * Retrieves the embedded asset model from an asset, if one exists.
	 *
	 * @deprecated Will be removed in next major version. Use the `get` method instead.
	 *
	 * @param Asset $asset
	 * @return mixed
	 */
	public function fromAsset(Asset $asset)
	{
		Craft::$app->getDeprecator()->log('craft.embeddedAssets.fromAsset', "The template method `craft.embeddedAssets.fromAsset` is now deprecated. Use `craft.embeddedAssets.get` instead.");

		return EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);
	}

	/**
	 * Retrieves the embedded asset models from an array of assets.
	 *
	 * @deprecated Will be removed in next major version. Use the `get` method instead.
	 *
	 * @param mixed $assets An iterable object of asset models.
	 * @param null $indexBy Whether to index the resulting array by a property of the asset.
	 * @return array
	 */
	public function fromAssets($assets, $indexBy = null): array
	{
		Craft::$app->getDeprecator()->log('craft.embeddedAssets.fromAssets', "The template method `craft.embeddedAssets.fromAssets` is now deprecated. Use `craft.embeddedAssets.get` instead.");

		$embeddedAssets = [];

		if (is_iterable($assets))
		{
			foreach ($assets as $asset)
			{
				$embeddedAsset = $asset instanceof Asset ?
					EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset) : null;

				if ($embeddedAsset)
				{
					if (is_string($indexBy) && $asset->hasProperty($indexBy))
					{
						$embeddedAssets[$asset->$indexBy] = $embeddedAsset;
					}
					else
					{
						$embeddedAssets[] = $embeddedAsset;
					}
				}
			}
		}

		return $embeddedAssets;
	}
}
