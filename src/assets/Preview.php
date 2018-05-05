<?php
namespace benf\embeddedassets\assets;

use craft\web\AssetBundle;

class Preview extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = '@benf/embeddedassets/resources';
		$this->js = ['preview.js'];

		parent::init();
	}
}
