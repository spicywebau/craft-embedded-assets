<?php

namespace Craft;

class EmbeddedAssetsVariable
{
	public function isEmbedded(AssetFileModel $asset)
	{
		return craft()->embeddedAssets->getEmbeddedAsset($asset) !== null;
	}

	public function fromAsset(AssetFileModel $asset)
	{
		return craft()->embeddedAssets->getEmbeddedAsset($asset);
	}

	public function fromAssets($assets, $indexBy = null)
	{
		return craft()->embeddedAssets->getEmbeddedAssets($assets, $indexBy);
	}
}
