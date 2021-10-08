<?php

namespace spicyweb\embeddedassets\console\controllers;

use Craft;
use craft\console\Controller;
use craft\elements\Asset;
use spicyweb\embeddedassets\Plugin as EmbeddedAssets;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Actions for batch-refreshing embedded asset data.
 *
 * @package spicyweb\embeddedassets\console\controllers
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.9.0
 */
class RefreshController extends Controller
{
    public $volume;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        $options = parent::options($actionID);

        if ($actionID === 'by-volume') {
            $options[] = 'volume';
        }

        return $options;
    }

    public function actionAll(): int
    {
        return $this->_refresh(null);
    }

    public function actionByVolume(): int
    {
        if ($this->volume === null) {
            $this->stderr('The --volume option must be specified with the by-volume action.' . PHP_EOL, Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return $this->_refresh($this->volume);
    }

    private function _refresh(?string $volume): int
    {
        $assetsService = Craft::$app->getAssets();
        $elementsService = Craft::$app->getElements();
        $embeddedAssets = [];
        $successCount = 0;
        $errorCount = 0;
        $assets = Asset::find()->kind('json');

        if ($volume !== null) {
            $assets->volume = explode(',', $volume);
        }

        foreach ($assets->all() as $asset) {
            if (($embeddedAsset = EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset)) !== null) {
                $embeddedAssets[$asset->id] = [
                    'asset' => $asset,
                    'embeddedAsset' => $embeddedAsset,
                ];
            }
        }

        $this->stdout(count($embeddedAssets) . ' embedded assets to be refreshed.' . PHP_EOL);

        foreach ($embeddedAssets as $assetId => $assetData) {
            $assetToReplace = $assetData['asset'];
            $embeddedAssetToReplace = $assetData['embeddedAsset'];
            $this->stdout('Refreshing ' . $assetToReplace->getPath() . ' ... '); 

            $folder = $assetToReplace->getFolder();
            $newEmbeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($embeddedAssetToReplace->url);
            $newAsset = EmbeddedAssets::$plugin->methods->createAsset($newEmbeddedAsset, $folder);
            $result = $elementsService->saveElement($newAsset);

            if (!$result) {
                $errorCount++;
                $errors = $newAsset->getFirstErrors();
                $this->stderr('Failed to save the Asset:' . PHP_EOL . implode(';' . PHP_EOL, $errors), Console::FG_RED);
            } else {
                $tempPath = $newAsset->getCopyOfFile();
                $assetsService->replaceAssetFile($assetToReplace, $tempPath, $assetToReplace->filename);
                $elementsService->deleteElement($newAsset);

                $successCount++;
                $this->stdout('done.' . PHP_EOL); 
            }
        }

        if ($successCount) {
            $this->stdout($successCount . ' embedded assets were refreshed.' . PHP_EOL);
        }

        if ($errorCount) {
            $this->stderr($errorCount . ' embedded assets failed to refresh.' . PHP_EOL, Console::FG_RED);
        }

        return $errorCount ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }
}
