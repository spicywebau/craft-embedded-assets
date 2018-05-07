<?php
namespace benf\embeddedassets;

use craft\elements\Asset;

use benf\embeddedassets\Plugin as EmbeddedAssets;

class Variable
{
	public function get(Asset $asset)
	{
		return EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);
	}

	/**
	 *
	 * @deprecated Will be removed in next major version.
	 *
	 * @param Asset $asset
	 * @return bool
	 */
	public function isEmbedded(Asset $asset): bool
	{
		return (bool)EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);
	}

	/**
	 *
	 * @deprecated Will be removed in next major version.
	 *
	 * @param Asset $asset
	 * @return mixed
	 */
	public function fromAsset(Asset $asset)
	{
		return EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);
	}

	/**
	 *
	 * @deprecated Will be removed in next major version.
	 *
	 * @param $assets
	 * @param null $indexBy
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
