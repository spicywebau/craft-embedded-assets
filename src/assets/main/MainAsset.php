<?php

namespace spicyweb\embeddedassets\assets\main;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class MainAsset
 *
 * @package spicyweb\embeddedassets\assets\main
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 3.1.0
 */
class MainAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'dist';
        $this->depends = [
            CpAsset::class,
        ];
        $this->css = [
            'styles' . DIRECTORY_SEPARATOR . 'main.css',
        ];
        $this->js = [
            'scripts' . DIRECTORY_SEPARATOR . 'main.js',
        ];

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view): void
    {
        $view->registerTranslations('embeddedassets', [
            'As a security measure embed codes will not be shown.',
            'Embed',
            'Replace',
            'This information is coming from an untrusted source.',
        ]);

        parent::registerAssetFiles($view);
    }
}

class_alias(MainAsset::class, \spicyweb\embeddedassets\assets\Main::class);
