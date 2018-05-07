<?php
namespace benf\embeddedassets\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class Main
 * @package benf\embeddedassets\assets
 */
class Main extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = '@benf/embeddedassets/resources';
		$this->depends = [ CpAsset::class ];
		$this->js = ['main.js'];

		parent::init();
	}
}
