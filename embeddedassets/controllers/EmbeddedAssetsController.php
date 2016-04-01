<?php

namespace Craft;

class EmbeddedAssetsController extends BaseController
{
	public function actionParseUrl()
	{
		$this->requireAjaxRequest();

		$url = craft()->request->getPost('url');
		$media = craft()->embeddedAssets->parseUrl($url);

		$json = array(
			'url' => $url,
		);

		if($media)
		{
			$json['success'] = true;
			$json['media'] = $media;
		}
		else
		{
			$json['success'] = false;
			$json['errors'] = array(Craft::t('Could not find any embeddable media from this URL.'));
		}

		$this->returnJson($json);
	}

	public function actionSaveEmbeddedAsset()
	{
		$this->requireAjaxRequest();

		$folderId = craft()->request->getPost('folderId');
		$url = craft()->request->getPost('url');
		$media = craft()->request->getPost('media');

		$model = new EmbeddedAssetsModel();

		$model->type            = $media['type'];
		$model->url             = $media['url'];
		$model->requestUrl      = $url;
		$model->title           = $media['title'];
		$model->description     = $media['description'];
		$model->authorName      = $media['authorName'];
		$model->authorUrl       = $media['authorUrl'];
		$model->providerName    = $media['providerName'];
		$model->providerUrl     = $media['providerUrl'];
		$model->cacheAge        = $media['cacheAge'];
		$model->thumbnailUrl    = $media['thumbnailUrl'];
		$model->thumbnailWidth  = $media['thumbnailWidth'];
		$model->thumbnailHeight = $media['thumbnailHeight'];
		$model->html            = $media['html'];
		$model->width           = $media['width'];
		$model->height          = $media['height'];

		$json = array();

		try
		{
			$success = craft()->embeddedAssets->saveEmbeddedAsset($model, $folderId);

			if($success)
			{
				$asset = craft()->assets->getFileById($model->id);

				$json['media'] = $model->getAttributes(null, true);
				$json['asset'] = $asset->getAttributes(null, true);
				$json['folderId'] = $folderId;
			}
			else
			{
				$json['errors'] = $model->getAllErrors();
			}

			$json['success'] = $success;
		}
		catch(\Exception $e)
		{
			$json['success'] = false;
			$json['errors'] = array($e->getMessage());
		}

		$this->returnJson($json);
	}
}
