<?php

namespace Craft;

class OEmbedController extends BaseController
{
	public function actionParseUrl()
	{
		$this->requireAjaxRequest();

		$url = craft()->request->getPost('url');
		$media = craft()->oEmbed->parseUrl($url);

		$json = array();

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

	public function actionSaveAsset()
	{
		$this->requireAjaxRequest();

		$folderId = craft()->request->getPost('folderId');
		$media = craft()->request->getPost('media');

		$model = new OEmbedModel();

		$model->type            = $media['type'];
		$model->version         = $media['version'];
		$model->url             = $media['url'];
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

		craft()->oEmbed->saveAsset($model, $folderId);
	}
}
