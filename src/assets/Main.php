<?php

namespace spicyweb\embeddedassets\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class Main
 *
 * @package spicyweb\embeddedassets\assets
 * @author Spicy Web <craft@spicyweb.com.au>
 * @author Benjamin Fleming
 * @since 1.0.0
 */
class Main extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@spicyweb/embeddedassets/resources';
        $this->depends = [CpAsset::class];
        $this->js = ['main.js'];
        
        parent::init();
    }
}
