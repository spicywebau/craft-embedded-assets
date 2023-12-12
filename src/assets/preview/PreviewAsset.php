<?php

namespace spicyweb\embeddedassets\assets\preview;

use craft\web\AssetBundle;

/**
 * Class PreviewAsset
 *
 * @package spicyweb\embeddedassets\assets\preview
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 3.1.0
 */
class PreviewAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'dist';
        $this->css = [
            'styles' . DIRECTORY_SEPARATOR . 'preview.css',
        ];
        $this->js = [
            'scripts' . DIRECTORY_SEPARATOR . 'preview.js',
        ];
        
        parent::init();
    }
}

class_alias(PreviewAsset::class, \spicyweb\embeddedassets\assets\Preview::class);
