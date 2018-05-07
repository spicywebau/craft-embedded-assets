<?php
namespace benf\embeddedassets;

use yii\web\BadRequestHttpException;
use yii\web\Response;

use Craft;
use craft\web\Controller as BaseController;

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

		$url = $requestService->getRequiredParam('url');
		$folderId = $requestService->getRequiredParam('folderId');

		$embeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($url);
		$folder = $assetsService->findFolder(['id' => $folderId]);

		if (!$folder)
		{
			throw new BadRequestHttpException('The target folder provided for uploading is not valid');
		}

		$userTempFolder = !$folder->volumeId ? $assetsService->getCurrentUserTemporaryUploadFolder() : null;
		if (!$userTempFolder || $folder->id != $userTempFolder->id)
		{
			$this->requirePermission("saveAssetInVolume:$folder->volumeId");
		}

		$asset = EmbeddedAssets::$plugin->methods->createAsset($embeddedAsset, $folder);
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
}
