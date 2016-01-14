<?php

namespace Craft;

class OEmbedController extends BaseController
{
	public function actionParseUrl()
	{
		$this->requireAjaxRequest();

		$url = craft()->request->getPost('url');

		$essence = new \Essence\Essence();
		$media = $essence->extract($url);

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
}
