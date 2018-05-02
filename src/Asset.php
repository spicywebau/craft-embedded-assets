<?php
namespace benf\embeddedassets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class Asset extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = '@benf/embeddedassets/resources';

		$this->depends = [
			CpAsset::class,
		];

		$this->js = [
			'main.js',
		];

		parent::init();
	}
}
