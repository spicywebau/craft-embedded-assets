<?php
namespace benf\embeddedassets;

use yii\base\Event;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\services\Assets;
use craft\web\View;
use craft\events\GetAssetThumbUrlEvent;
use craft\events\TemplateEvent;

use benf\embeddedassets\assets\Main as MainAsset;
use benf\embeddedassets\models\Settings;

class Plugin extends BasePlugin
{
	public static $plugin;

	public $hasCpSettings = true;

	public $controllerMap = [
		'actions' => Controller::class,
	];

	public function init()
	{
		parent::init();

		self::$plugin = $this;

		$requestService = Craft::$app->getRequest();

		$this->setComponents([
            'methods' => Service::class,
        ]);

		if ($requestService->getIsCpRequest())
		{
			Event::on(
				View::class,
				View::EVENT_BEFORE_RENDER_TEMPLATE,
				function(TemplateEvent $event)
				{
					$viewService = Craft::$app->getView();
					$viewService->registerAssetBundle(MainAsset::class);
				}
			);

			Event::on(
				Assets::class,
				Assets::EVENT_GET_ASSET_THUMB_URL,
				function(GetAssetThumbUrlEvent $event)
				{
					// $event->url = '';
				}
			);
		}
	}

	protected function createSettingsModel()
	{
		return new Settings();
	}

	protected function settingsHtml()
    {
		$viewService = Craft::$app->getView();

		return $viewService->renderTemplate('embeddedassets/settings', [
			'settings' => $this->getSettings(),
		]);
    }
}
