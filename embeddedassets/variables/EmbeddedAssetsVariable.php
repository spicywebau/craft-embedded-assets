<?php

namespace Craft;

class EmbeddedAssetsVariable
{
	public function isEmbeddedAsset(AssetFileModel $asset)
	{
		return craft()->embeddedAssets->getEmbeddedAsset($asset) !== null;
	}

	public function getEmbeddedAsset(AssetFileModel $asset)
	{
		return craft()->embeddedAssets->getEmbeddedAsset($asset);
	}

	public function getEmbeddedAssets($assets, $indexBy = null)
	{
		return craft()->embeddedAssets->getEmbeddedAssets($assets, $indexBy);
	}
}
