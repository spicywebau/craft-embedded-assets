<?php
namespace benf\embeddedassets;

use yii\web\Response;

use Craft;
use craft\web\Controller as BaseController;

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
			$response['success'] = true;
			$response['payload'] = [
				'info' => $embeddedAsset,
				'isSafe' => $embeddedAsset->isSafe(),
			];
		}

		return $this->asJson($response);
	}
}
