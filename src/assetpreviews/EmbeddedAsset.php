<?php

namespace spicyweb\embeddedassets\assetpreviews;

use Craft;
use craft\base\AssetPreviewHandler;
use spicyweb\embeddedassets\Plugin as EmbeddedAssets;

/**
 * Asset preview handler for Embedded Assets.
 *
 * @package spicyweb\embeddedassets\assetpreviews
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.2.2
 */
class EmbeddedAsset extends AssetPreviewHandler
{
    /**
     * @inheritdoc
     */
    public function getPreviewHtml(array $variables = []): string
    {
        $embeddedAsset = EmbeddedAssets::$plugin->methods->getEmbeddedAsset($this->asset);

        if ($embeddedAsset !== null) {
            return Craft::$app->getView()->renderTemplate('embeddedassets/_previews/default', [
                'embeddedAsset' => $embeddedAsset,
                'showContent' => false,
                'inIframe' => false,
            ]);
        }

        return '';
    }
}
