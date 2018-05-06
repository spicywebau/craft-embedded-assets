<?php
namespace benf\embeddedassets;

use yii\web\BadRequestHttpException;
use yii\web\Response;

use Craft;
use craft\web\Controller as BaseController;
use craft\elements\Asset;
use craft\helpers\Assets;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\models\VolumeFolder;

use benf\embeddedassets\Plugin as EmbeddedAssets;
use benf\embeddedassets\assets\Preview as PreviewAsset;

class Controller extends BaseController
{
	public function actionSave(): Response
	{
		$this->requireAcceptsJson();

		$response = null;

		$assetsService = Craft::$app->getAssets();
		$elementsService = Craft::$app->getElements();
		$requestService = Craft::$app->getRequest();

		$pluginSettings = EmbeddedAssets::$plugin->getSettings();

		$url = $requestService->getRequiredParam('url');
		$folderId = $requestService->getRequiredParam('folderId');

		$embeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($url);
		$folder = $assetsService->findFolder(['id' => $folderId]);

		if (!$folder)
		{
			throw new BadRequestHttpException('The target folder provided for uploading is not valid');
		}

		$this->_requirePermissionByFolder('saveAssetInVolume', $folder);

		$tempFilePath = Assets::tempFilePath();
		$fileContents = Json::encode($embeddedAsset, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

		FileHelper::writeToFile($tempFilePath, $fileContents);

		$assetTitle = $embeddedAsset->title ?: $embeddedAsset->url;

		$fileName = Assets::prepareAssetName($assetTitle, false);
		$fileName = str_replace('.', '', $fileName);
		$fileName = $fileName ?: 'embedded-asset';
		$fileName = StringHelper::safeTruncate($fileName, $pluginSettings->maxFileNameLength) . '.json';
		$fileName = $assetsService->getNameReplacementInFolder($fileName, $folderId);

		$asset = new Asset();
		$asset->title = StringHelper::safeTruncate($assetTitle, $pluginSettings->maxAssetNameLength);
		$asset->tempFilePath = $tempFilePath;
		$asset->filename = $fileName;
		$asset->newFolderId = $folder->id;
		$asset->volumeId = $folder->volumeId;
		$asset->avoidFilenameConflicts = true;
		$asset->setScenario(Asset::SCENARIO_CREATE);

		$result = $elementsService->saveElement($asset);

		// In case of error, let user know about it.
		if (!$result)
		{
			$errors = $asset->getFirstErrors();
			$response = $this->asErrorJson(Craft::t('app', "Failed to save the Asset:") . implode(";\n", $errors));
		}
		else
		{
			$response = $this->asJson([
				'success' => true,
				'payload' => [
					'assetId' => $asset->id,
					'folderId' => $folderId,
				],
			]);
		}

		return $response;
	}

	public function actionPreview(): Response
	{
		$requestService = Craft::$app->getRequest();
		$viewService = Craft::$app->getView();
		$viewService->registerAssetBundle(PreviewAsset::class);

		$url = $requestService->getRequiredParam('url');
		$callback = $requestService->getParam('callback');
		$embeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($url);

		$template = $viewService->renderTemplate('embeddedassets/_preview', [
			'embeddedAsset' => $embeddedAsset,
			'callback' => $callback,
		]);

		$response = $this->asRaw($template);
		$headers = $response->getHeaders();
		$headers->set('content-type', 'text/html; charset=utf-8');

		return $response;
	}

	/**
	 * Require an Assets permissions.
	 *
	 * @param string $permissionName Name of the permission to require.
	 * @param VolumeFolder $folder Folder on the Volume on which to require the permission.
	 */
	private function _requirePermissionByFolder(string $permissionName, VolumeFolder $folder)
	{
		if (!$folder->volumeId) {
			$userTemporaryFolder = Craft::$app->getAssets()->getCurrentUserTemporaryUploadFolder();

			// Skip permission check only if it's the user's temporary folder
			if ($userTemporaryFolder->id == $folder->id) {
				return;
			}
		}

		$this->_requirePermissionByVolumeId($permissionName, $folder->volumeId);
	}

	/**
	 * Require an Assets permissions.
	 *
	 * @param string $permissionName Name of the permission to require.
	 * @param int $volumeId The Volume id on which to require the permission.
	 */
	private function _requirePermissionByVolumeId(string $permissionName, int $volumeId)
	{
		$this->requirePermission($permissionName.':'.$volumeId);
	}
}
