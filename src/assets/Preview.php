<?php

namespace spicyweb\embeddedassets\assets;

use craft\web\AssetBundle;

/**
 * Class Preview
 *
 * @package spicyweb\embeddedassets\assets
 * @author Spicy Web <craft@spicyweb.com.au>
 * @author Benjamin Fleming
 * @since 1.0.0
 */
class Preview extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@spicyweb/embeddedassets/resources';
        $this->js = ['preview.js'];
        
        parent::init();
    }
}
