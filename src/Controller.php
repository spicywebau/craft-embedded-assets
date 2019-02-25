<?php
namespace benf\embeddedassets;

use yii\web\BadRequestHttpException;
use yii\web\Response;

use Craft;
use craft\web\Controller as BaseController;
use craft\helpers\Template;

use benf\embeddedassets\Plugin as EmbeddedAssets;
use benf\embeddedassets\assets\Preview as PreviewAsset;

/**
 * Class Controller
 * @package benf\embeddedassets
 */
class Controller extends BaseController
{
	/**
	 * Saves an embedded asset as a Craft asset.
	 *
	 * @query string url The URL to create an embedded asset from (required).
	 * @query int folderId The volume folder ID to save the asset to (required).
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

		$assetsService = Craft::$app->getAssets();
		$elementsService = Craft::$app->getElements();
		$requestService = Craft::$app->getRequest();

		$url = $requestService->getRequiredParam('url');
		$folderId = $requestService->getRequiredParam('folderId');

		$embeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($url);
		$folder = $assetsService->findFolder(['uid' => $folderId]);

		if (!$folder)
		{
			throw new BadRequestHttpException('The target folder provided for uploading is not valid');
		}

		$userTempFolder = !$folder->volumeId ? $assetsService->getCurrentUserTemporaryUploadFolder() : null;
		if (!$userTempFolder || $folder->id != $userTempFolder->id)
		{
			$volume = Craft::$app->getVolumes()->getVolumeById($folder->volumeId); 
			$this->requirePermission('saveAssetInVolume:'. $volume->uid);
		}

		$asset = EmbeddedAssets::$plugin->methods->createAsset($embeddedAsset, $folder);
		$result = $elementsService->saveElement($asset);

		if (!$result)
		{
			$errors = $asset->getFirstErrors();
			$errorLabel = Craft::t('app', "Failed to save the Asset:");
			$response = $this->asErrorJson($errorLabel . implode(";\n", $errors));
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
	 * @throws \Twig_Error_Loader
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

		if ($url)
		{
			$embeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($url);
		}
		else if ($assetId)
		{
			$asset = $assetsService->getAssetById($assetId);

			if (!$asset)
			{
				throw new BadRequestHttpException("Could not find asset with ID: $assetId");
			}

			$embeddedAsset = EmbeddedAssets::$plugin->methods->getEmbeddedAsset($asset);

			if (!$embeddedAsset)
			{
				throw new BadRequestHttpException("Could not find embedded asset from asset $assetId ($asset->filename)");
			}
		}
		else
		{
			throw new BadRequestHttpException("URL or asset ID are missing from the request");
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
}
