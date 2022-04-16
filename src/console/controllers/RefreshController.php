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
     * @var string|null
     * @since 2.10.0
     */
    public $provider;

    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);

        if (in_array($actionID, ['all', 'by-volume'])) {
            $options[] = 'volume';
        }

        if (in_array($actionID, ['all', 'by-provider'])) {
            $options[] = 'provider';
        }

        return $options;
    }

    public function actionAll(): int
    {
        return $this->_refresh(
            $this->volume ? explode(',', $this->volume) : null,
            $this->provider ? explode(',', $this->provider) : null
        );
    }

    public function actionByVolume(): int
    {
        if ($this->volume === null) {
            $this->stderr('The --volume option must be specified with the by-volume action.' . PHP_EOL, Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return $this->_refresh(explode(',', $this->volume), null);
    }

    /**
     * Refreshes embedded assets by provider. The provider(s) must match the `providerName` of the embedded assets.
     *
     * @return int
     * @since 2.10.0
     */
    public function actionByProvider(): int
    {
        if ($this->provider === null) {
            $this->stderr('The --provider option must be specified with the by-provider action.' . PHP_EOL, Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return $this->_refresh(null, explode(',', $this->provider));
    }

    private function _refresh(?array $volume, ?array $providers): int
    {
        $assetsService = Craft::$app->getAssets();
        $elementsService = Craft::$app->getElements();
        $embeddedAssets = [];
        $successCount = 0;
        $errorCount = 0;
        $assets = Asset::find()->kind('json');

        if ($volume !== null) {
            $assets->volume($volume);
        }

        $providersKeys = [];

        if ($providers !== null) {
            foreach ($providers as $provider) {
                $providersKeys[$provider] = true;
            }
        }

        foreach ($assets->all() as $asset) {
            $embeddedAsset = EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);

            if ($embeddedAsset !== null && ($providers === null || isset($providersKeys[$embeddedAsset->providerName]))) {
                $embeddedAssets[$asset->id] = [
                    'asset' => $asset,
                    'embeddedAsset' => $embeddedAsset,
                ];
            }
        }

        $count = count($embeddedAssets);
        if ($count > 1) {
            $this->stdout($count . ' embedded assets to be refreshed.' . PHP_EOL);
        } else {
            $this->stdout($count . ' embedded asset to be refreshed.' . PHP_EOL);
        }

        foreach ($embeddedAssets as $assetId => $assetData) {
            $assetToReplace = $assetData['asset'];
            $embeddedAssetToReplace = $assetData['embeddedAsset'];
            $this->stdout('Refreshing ' . $assetToReplace->getPath() . ' ... ');

            $folder = $assetToReplace->getFolder();
            $newEmbeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($embeddedAssetToReplace->url, false);
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
            if ($successCount > 1) {
                $this->stdout($successCount . ' embedded assets were refreshed.' . PHP_EOL);
            } else {
                $this->stdout($successCount . ' embedded asset was refreshed.' . PHP_EOL);
            }
        }

        if ($errorCount) {
            if ($errorCount > 1) {
                $this->stderr($errorCount . ' embedded assets failed to refresh.' . PHP_EOL, Console::FG_RED);
            } else {
                $this->stderr($errorCount . ' embedded asset failed to refresh.' . PHP_EOL, Console::FG_RED);
            }
        }

        return $errorCount ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }
}
