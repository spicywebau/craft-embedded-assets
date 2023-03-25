<?php

namespace spicyweb\embeddedassets;

use Craft;
use craft\db\Table;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\models\VolumeFolder;
use craft\web\Controller as BaseController;
use spicyweb\embeddedassets\assets\Preview as PreviewAsset;
use spicyweb\embeddedassets\errors\NotWhitelistedException;
use spicyweb\embeddedassets\Plugin as EmbeddedAssets;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Class Controller
 *
 * @package spicyweb\embeddedassets
 * @author Spicy Web <craft@spicyweb.com.au>
 * @author Benjamin Fleming
 * @since 1.0.0
 */
class Controller extends BaseController
{
    /**
     * Saves an embedded asset as a Craft asset.
     *
     * @query string url The URL to create an embedded asset from (required).
     * @query string targetType Whether `targetUid` belongs to a volume or a folder (required).
     * @query string targetUid The volume or folder UID to save the asset to (required).
     * @response JSON
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSave(): Response
    {
        $this->requireAcceptsJson();

        $response = null;

        $requestService = Craft::$app->getRequest();

        $url = $requestService->getRequiredBodyParam('url');
        $targetType = $requestService->getRequiredBodyParam('targetType');
        $targetUid = $requestService->getBodyParam('targetUid');
        $targetId = $requestService->getBodyParam('targetId');

        if (!$targetId && !$targetUid) {
            throw new BadRequestHttpException('One of targetUid or targetId is required.');
        }

        $folderCriteria = [];

        if ($targetId) {
            $folderCriteria['id'] = $targetId;
        } elseif (str_ends_with($targetType, 'folder')) {
            $folderCriteria['uid'] = $targetUid;
        } else {
            $folderCriteria['volumeId'] = Db::idByUid(Table::VOLUMES, $targetUid);
            $folderCriteria['parentId'] = ':empty:';
        }

        $folder = $this->_findFolder($folderCriteria);
        $embeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($url);
        $asset = EmbeddedAssets::$plugin->methods->createAsset($embeddedAsset, $folder);
        $result = Craft::$app->getElements()->saveElement($asset);

        if (!$result) {
            $errors = $asset->getFirstErrors();
            $errorLabel = Craft::t('app', "Failed to save the Asset:");
            $response = $this->asErrorJson($errorLabel . implode(";\n", $errors));
        } else {
            $response = $this->asJson([
                'success' => true,
                'payload' => [
                    'assetId' => $asset->id,
                    'folderUid' => $folder->uid,
                ],
            ]);
        }

        return $response;
    }
    
    /**
     * Replaces an embedded asset.
     *
     * @query string url The URL to create an embedded asset from (required).
     * @query string targetType Whether `targetUid` belongs to a volume or a folder (required).
     * @query string targetUid The volume or folder UID to save the asset to (required).
     * @query int assetId The asset ID to replace (required).
     * @response JSON
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionReplace(): Response
    {
        $this->requireAcceptsJson();

        $response = null;

        $assetsService = Craft::$app->getAssets();
        $elementsService = Craft::$app->getElements();
        $requestService = Craft::$app->getRequest();

        $url = $requestService->getRequiredParam('url');
        $targetType = $requestService->getRequiredBodyParam('targetType');
        $targetUid = $requestService->getBodyParam('targetUid');
        $targetId = $requestService->getBodyParam('targetId');
        $assetId = $requestService->getRequiredParam('assetId');

        if (!$targetId && !$targetUid) {
            throw new BadRequestHttpException('One of targetUid or targetId is required.');
        }

        $assetToReplace = null;

        if ($assetId && !$assetToReplace = $assetsService->getAssetById($assetId)) {
            throw new NotFoundHttpException('Asset not found.');
        }

        $folderCriteria = [];

        if ($targetId) {
            $folderCriteria['id'] = $targetId;
        } elseif (str_ends_with($targetType, 'folder')) {
            $folderCriteria['uid'] = $targetUid;
        } else {
            $folderCriteria['volumeId'] = Db::idByUid(Table::VOLUMES, $targetUid);
            $folderCriteria['parentId'] = ':empty:';
        }

        $folder = $this->_findFolder($folderCriteria);
        $embeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($url);
        $asset = EmbeddedAssets::$plugin->methods->createAsset($embeddedAsset, $folder);
        $result = $elementsService->saveElement($asset);

        $tempPath = $asset->getCopyOfFile();
        $assetToReplace->title = $asset->title;
        $assetToReplace->newFilename = $asset->filename;
        $assetsService->replaceAssetFile($assetToReplace, $tempPath, $assetToReplace->newFilename);
        $elementsService->deleteElement($asset);

        if (!$result) {
            $errors = $asset->getFirstErrors();
            $errorLabel = Craft::t('app', "Failed to save the Asset:");
            $response = $this->asErrorJson($errorLabel . implode(";\n", $errors));
        } else {
            // Replace the old cached data for the embedded asset
            Craft::$app->getCache()->set(
                EmbeddedAssets::$plugin->methods->getCachedAssetKey($assetToReplace),
                Json::encode($embeddedAsset->jsonSerialize(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            );

            $response = $this->asJson([
                'success' => true,
                'payload' => [
                    'assetId' => $asset->id,
                    'folderUid' => $folder->uid,
                ],
            ]);
        }

        return $response;
    }

    /**
     * Renders a preview of the embedded asset.
     *
     * @query string url The URL to create an embedded asset from.
     * @query string assetId The asset ID to load the embedded asset from.
     * @query string callback The name of a global Javascript function to be called when the preview is loaded.
     * @response HTML
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws \Twig\Error\LoaderError
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionPreview(): Response
    {
        $assetsService = Craft::$app->getAssets();
        $requestService = Craft::$app->getRequest();
        $viewService = Craft::$app->getView();
        $viewService->registerAssetBundle(PreviewAsset::class);

        $url = $requestService->getParam('url');
        $assetId = $requestService->getParam('assetId');
        $callback = $requestService->getParam('callback');
        $showContent = (bool)$requestService->getParam('showContent', true);

        if ($url) {
            try {
                $embeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($url);
            } catch (NotWhitelistedException $e) {
                // We'll still show the preview, we just won't allow saving it from the frontend
                $embeddedAsset = $e->embeddedAsset;
            }
        } else {
            if ($assetId) {
                $asset = $assetsService->getAssetById($assetId);

                if (!$asset) {
                    throw new BadRequestHttpException("Could not find asset with ID: $assetId");
                }

                $embeddedAsset = EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);

                if (!$embeddedAsset) {
                    throw new BadRequestHttpException("Could not find embedded asset from asset $assetId ($asset->filename)");
                }
            } else {
                throw new BadRequestHttpException("URL or asset ID are missing from the request");
            }
        }

        $template = $viewService->renderTemplate('embeddedassets/_preview', [
            'embeddedAsset' => $embeddedAsset,
            'callback' => $callback,
            'showContent' => $showContent,
        ]);

        $response = $this->asRaw($template);
        $headers = $response->getHeaders();
        $headers->set('content-type', 'text/html; charset=utf-8');

        return $response;
    }

    private function _findFolder(array $criteria): VolumeFolder
    {
        $assetsService = Craft::$app->getAssets();
        $folder = $assetsService->findFolder($criteria);

        if (!$folder) {
            throw new BadRequestHttpException('The target folder provided for uploading is not valid');
        }

        $userTempFolder = !$folder->volumeId ? $assetsService->getUserTemporaryUploadFolder() : null;

        if (!$userTempFolder || $folder->id != $userTempFolder->id) {
            $volume = Craft::$app->getVolumes()->getVolumeById($folder->volumeId);
            $this->requirePermission('saveAssets:' . $volume->uid);
        }

        return $folder;
    }
}
