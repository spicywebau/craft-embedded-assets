<?php
namespace benf\embeddedassets;

use yii\base\Event;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\web\View;
use craft\events\TemplateEvent;

use benf\embeddedassets\Service;
use benf\embeddedassets\Controller;
use benf\embeddedassets\Asset;
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

					$viewService->registerAssetBundle(Asset::class);
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
