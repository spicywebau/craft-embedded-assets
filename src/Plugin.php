<?php
namespace benf\embeddedassets;

use Craft;
use craft\base\Plugin as BasePlugin;

use benf\embeddedassets\Service;
use benf\embeddedassets\Controller;
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

		$this->setComponents([
            'methods' => Service::class,
        ]);
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
