<?php
namespace benf\embeddedassets;

use yii\web\NotFoundHttpException;
use yii\web\Response;

use Craft;
use craft\web\Controller as BaseController;
use craft\helpers\UrlHelper;

use benf\embeddedassets\assets\Preview as PreviewAsset;

use benf\embeddedassets\Plugin as EmbeddedAssets;

class Controller extends BaseController
{
	public function actionRequestUrl(): Response
	{
		$this->requireAcceptsJson();

		$requestService = Craft::$app->getRequest();

		$url = $requestService->getRequiredParam('url');
		$embeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($url);

		$response = [
			'success' => false,
			'payload' => null,
		];

		if ($embeddedAsset)
		{
			$isSafe = $embeddedAsset->isSafe();
			$previewUrl = UrlHelper::actionUrl('embeddedassets/actions/preview-url', ['url' => $url]);

			$response['success'] = true;
			$response['payload'] = [
				'info' => $embeddedAsset,
				'previewUrl' => $isSafe ? $previewUrl : false,
			];
		}

		return $this->asJson($response);
	}

	public function actionPreviewUrl(): Response
	{
		$requestService = Craft::$app->getRequest();
		$viewService = Craft::$app->getView();
		$viewService->registerAssetBundle(PreviewAsset::class);

		$url = $requestService->getRequiredParam('url');
		$callback = $requestService->getParam('callback');
		$embeddedAsset = EmbeddedAssets::$plugin->methods->requestUrl($url);

		$response = Craft::$app->getResponse();
		$headers = $response->getHeaders();
		$headers->set('content-type', 'text/html; charset=utf-8');
		$response->format = Response::FORMAT_RAW;
		$response->data = $viewService->renderTemplate('embeddedassets/_preview', [
			'embeddedAsset' => $embeddedAsset,
			'callback' => $callback,
		]);

		return $response;
	}
}
