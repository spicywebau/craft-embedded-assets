<?php
namespace benf\embeddedassets;

use benf\embeddedassets\models\EmbeddedAsset;
use craft\elements\Asset;

use benf\embeddedassets\Plugin as EmbeddedAssets;

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
