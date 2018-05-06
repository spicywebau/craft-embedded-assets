<?php
namespace benf\embeddedassets;

use craft\base\Element;
use craft\elements\Asset;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\SetElementTableAttributeHtmlEvent;
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
					$assetManagerService = Craft::$app->getAssetManager();

					$embeddedAsset = $this->methods->getEmbeddedAsset($event->asset);

					if ($embeddedAsset)
					{
						$thumbSize = max($event->width, $event->height);
						$isSafe = $this->methods->checkWhitelist($embeddedAsset->url);
						$image = $isSafe ? $embeddedAsset->getImageToSize($thumbSize) : null;

						if ($image)
						{
							$event->url = $image['url'];
						}
						// Check to avoid showing the default thumbnail or provider icon in the asset editor HUD.
						else if ($thumbSize <= 200)
						{
							$providerIcon = $isSafe ? $embeddedAsset->getProviderIconToSize($thumbSize) : null;
							$event->url = $providerIcon ? $providerIcon['url'] :
								$assetManagerService->getPublishedUrl('@benf/embeddedassets/resources/default-thumb.svg', true);
						}
					}
				}
			);

			Event::on(
				Asset::class,
				Asset::EVENT_REGISTER_TABLE_ATTRIBUTES,
				function(RegisterElementTableAttributesEvent $event)
				{
					$event->tableAttributes['provider'] = ['label' => Craft::t('embeddedassets', "Provider")];
				}
			);

			Event::on(
				Asset::class,
				Asset::EVENT_SET_TABLE_ATTRIBUTE_HTML,
				function(SetElementTableAttributeHtmlEvent $event)
				{
					$assetManagerService = Craft::$app->getAssetManager();

					$embeddedAsset = $this->methods->getEmbeddedAsset($event->sender);

					switch ($event->attribute)
					{
						case 'provider':
						{
							if ($embeddedAsset && $embeddedAsset->providerName)
							{
								$providerIcon = $embeddedAsset->getProviderIconToSize(32);

								$event->html = "<span class='embedded-assets_label'>";

								if ($providerIcon)
								{
									$providerIconUrl = $providerIcon['url'];
									$providerIconWidth = $providerIcon['width'];
									$providerIconHeight = $providerIcon['height'];

									$event->html .= "<img src='$providerIconUrl' width='16' height='16'>";
								}

								if ($embeddedAsset->providerUrl)
								{
									$event->html .= "<a href='$embeddedAsset->providerUrl' target='_blank' rel='noopener'>";
								}

								$event->html .= $embeddedAsset->providerName;

								if ($embeddedAsset->providerUrl)
								{
									$event->html .= "</a>";
								}

								$event->html .= "</span>";
							}
							else
							{
								$event->html = '';
							}
						}
						break;
						case 'kind':
						{
							if ($embeddedAsset && $embeddedAsset->type)
							{
								$event->html = Craft::t('embeddedassets', ucfirst($embeddedAsset->type));
							}
						}
						break;
						case 'width':
						{
							if ($embeddedAsset && $embeddedAsset->imageWidth)
							{
								$event->html = $embeddedAsset->imageWidth . 'px';
							}
						}
						break;
						case 'height':
						{
							if ($embeddedAsset && $embeddedAsset->imageHeight)
							{
								$event->html = $embeddedAsset->imageHeight . 'px';
							}
						}
						break;
						case 'imageSize':
						{
							if ($embeddedAsset && $embeddedAsset->imageWidth && $embeddedAsset->imageHeight)
							{
								$width = $embeddedAsset->imageWidth;
								$height = $embeddedAsset->imageHeight;
								$event->html = "$width × $height";
							}
						}
						break;
						case 'link':
						{
							if ($embeddedAsset && $embeddedAsset->url)
							{
								$linkTitle = Craft::t('app', 'Visit webpage');
								$event->html = "<a href='$embeddedAsset->url' target='_blank' rel='noopener' data-icon='world' title='$linkTitle'></a>";
							}
						}
						break;
					}
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
