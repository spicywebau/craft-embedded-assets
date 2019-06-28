<?php
namespace spicyweb\embeddedassets;

use yii\base\Event;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\services\Assets;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;
use craft\elements\Asset;
use craft\helpers\UrlHelper;
use craft\events\GetAssetThumbUrlEvent;
use craft\events\TemplateEvent;
use craft\events\SetElementTableAttributeHtmlEvent;
use craft\events\RegisterElementHtmlAttributesEvent;
use craft\events\RegisterElementTableAttributesEvent;

use spicyweb\embeddedassets\assets\Main as MainAsset;
use spicyweb\embeddedassets\models\Settings;
use spicyweb\embeddedassets\models\EmbeddedAsset;

/**
 * Class Plugin
 *
 * @package spicyweb\embeddedassets
 * @author Spicy Web <craft@spicyweb.com.au>
 * @author Benjamin Fleming
 * @since 1.0.0
 */
class Plugin extends BasePlugin
{
	/**
	 * @var Plugin The instance of this plugin (alias for Plugin::getPlugin()).
	 */
	public static $plugin;

	/**
	 * @var string
	 */
	public $changelogUrl = 'https://raw.githubusercontent.com/spicywebau/craft-embedded-assets/master/CHANGELOG.md';

	/**
	 * @var string
	 */
	public $downloadUrl = 'https://github.com/spicywebau/craft-embedded-assets/archive/master.zip';

	/**
	 * @var bool
	 */
	public $hasCpSettings = true;

	/**
	 * @var array
	 */
	public $controllerMap = [
		'actions' => Controller::class,
	];

	private $defaultThumbnailUrl = "";

	/**
	 * Plugin initializer.
	 */
	public function init()
	{
		parent::init();

		self::$plugin = $this;

		$requestService = Craft::$app->getRequest();

		$this->setComponents([
            'methods' => Service::class,
        ]);

		$this->_configureTemplateVariable();

		if ($requestService->getIsCpRequest())
		{
			$assetManagerService = Craft::$app->getAssetManager();
			$this->defaultThumbnailUrl = $assetManagerService->getPublishedUrl('@spicyweb/embeddedassets/resources/default-thumb.svg', true);
			
			$this->_configureCpResources();
			$this->_configureAssetThumbnails();

			// if showThumbnailsInCp is set to true add in the asset index attribute for the thumbnails
			if($this->getSettings()->showThumbnailsInCp) {
				$this->_configureAssetIndexAttributes();
			}
		}
	}

	/**
	 * @return Settings
	 */
	protected function createSettingsModel()
	{
		return new Settings();
	}

	/**
	 * @return null|string
	 * @throws \Twig_Error_Loader
	 * @throws \yii\base\Exception
	 */
	protected function settingsHtml()
    {
		$viewService = Craft::$app->getView();

		return $viewService->renderTemplate('embeddedassets/settings', [
			'settings' => $this->getSettings(),
		]);
    }

	/**
	 * Includes the control panel front end resources (resources/main.js).
	 */
	private function _configureCpResources()
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
	}

	/**
	 * Assigns the template variable so it can be accessed in the templates at `craft.embeddedAssets`.
	 */
    private function _configureTemplateVariable()
	{
		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			function(Event $event)
			{
				$event->sender->set('embeddedAssets', Variable::class);
			}
		);
	}

	/**
	 * Sets the embedded asset thumbnails on asset elements in the control panel.
	 */
	private function _configureAssetThumbnails()
	{
		Event::on(
			Assets::class,
			Assets::EVENT_GET_ASSET_THUMB_URL,
			function(GetAssetThumbUrlEvent $event)
			{
				// if showThumbnailsInCp is not true, return the default thumbnail url.
				// else retrieve the thumbnail.
				// this is done so it doesn't prolong the query times by retrieving the thumbnail from the embedded asset.
				if(!$this->getSettings()->showThumbnailsInCp) {
					$event->url = $this->defaultThumbnailUrl;
				} else {
					$embeddedAsset = $this->methods->getEmbeddedAsset($event->asset);
					$thumbSize = max($event->width, $event->height);
					$thumbnailUrl = $embeddedAsset ? $this->_getThumbnailUrl($embeddedAsset, $thumbSize) : null;
	
					if ($thumbnailUrl)
					{
						$event->url = $thumbnailUrl;
					}
				}
			}
		);
	}

	/**
	 * Adds new and modifies existing asset table attributes in the control panel.
	 */
    private function _configureAssetIndexAttributes()
	{
		$newAttributes = [
			'provider' => "Provider",
		];

		Event::on(
			Asset::class,
			Asset::EVENT_REGISTER_HTML_ATTRIBUTES,
			function(RegisterElementHtmlAttributesEvent $event)
			{
				$embeddedAsset = $this->methods->getEmbeddedAsset($event->sender);

				if ($embeddedAsset && $embeddedAsset->code && $embeddedAsset->isSafe())
				{
					// Setting `null` actually adds the attribute, but doesn't include a value
					$event->htmlAttributes['data-embedded-asset'] = $embeddedAsset->aspectRatio;
				}
			}
		);

		Event::on(
			Asset::class,
			Asset::EVENT_REGISTER_TABLE_ATTRIBUTES,
			function(RegisterElementTableAttributesEvent $event) use($newAttributes)
			{
				foreach ($newAttributes as $attributeHandle => $attributeLabel)
				{
					$event->tableAttributes[$attributeHandle] = [
						'label' => Craft::t('embeddedassets', $attributeLabel),
					];
				}
			}
		);

		Event::on(
			Asset::class,
			Asset::EVENT_SET_TABLE_ATTRIBUTE_HTML,
			function(SetElementTableAttributeHtmlEvent $event) use($newAttributes)
			{
				// Prevent new table attributes from causing server errors
				if (array_key_exists($event->attribute, $newAttributes))
				{
					$event->html = '';
				}

				$embeddedAsset = $this->methods->getEmbeddedAsset($event->sender);
				$html = $embeddedAsset ? $this->_getTableAttributeHtml($embeddedAsset, $event->attribute) : null;

				if ($html !== null)
				{
					$event->html = $html;
				}
			}
		);
	}

	/**
	 * Helper method for retrieving an appropriately sized thumbnail from an embedded asset.
	 *
	 * @param EmbeddedAsset $embeddedAsset
	 * @param int $size The preferred size of the thumbnail.
	 * @param int $maxSize The largest size to bother getting a thumbnail for.
	 * @return false|string The URL to the thumbnail.
	 */
    private function _getThumbnailUrl(EmbeddedAsset $embeddedAsset, int $size, int $maxSize = 200)
	{
		$defaultThumb = $this->defaultThumbnailUrl;

		// If embedded asset thumbnails are disabled, just show Embedded Assets' default.
		if (!$this->getSettings()->showThumbnailsInCp)
		{
			return $defaultThumb;
		}

		$url = false;
		$image = $embeddedAsset->getImageToSize($size);

		if ($image && UrlHelper::isAbsoluteUrl($image['url']))
		{
			$imageSize = max($image['width'], $image['height']);

			if ($size <= $maxSize || $imageSize > $maxSize)
			{
				$url = $image['url'];
			}
		}
		// Check to avoid showing the default thumbnail or provider icon in the asset editor HUD.
		else if ($size <= $maxSize)
		{
			$providerIcon = $embeddedAsset->getProviderIconToSize($size);

			if ($providerIcon && UrlHelper::isAbsoluteUrl($providerIcon['url']))
			{
				$url = $providerIcon['url'];
			}
			else
			{
				$url = $defaultThumb;
			}
		}

		return $url;
	}

	/**
	 * Outputs the HTML for each asset table attribute for the control panel.
	 *
	 * @param EmbeddedAsset $embeddedAsset
	 * @param string $attribute
	 * @return null|string
	 */
    private function _getTableAttributeHtml(EmbeddedAsset $embeddedAsset, string $attribute)
	{
		$html = null;

		switch ($attribute)
		{
			case 'provider':
			{
				if ($embeddedAsset->providerName)
				{
					$providerUrl = $embeddedAsset->providerUrl;
					$providerIcon = $embeddedAsset->getProviderIconToSize(32);
					$providerIconUrl = $providerIcon ? $providerIcon['url'] : null;
					$isProviderIconSafe = $providerIconUrl && UrlHelper::isAbsoluteUrl($providerIconUrl);

					$html = "<span class='embedded-assets_label'>";
					$html .= $isProviderIconSafe ? "<img src='$providerIconUrl' width='16' height='16'>" : '';
					$html .= $providerUrl ? "<a href='$embeddedAsset->providerUrl' target='_blank' rel='noopener'>" : '';
					$html .= $embeddedAsset->providerName;
					$html .= $providerUrl ? "</a>" : '';
					$html .= "</span>";
				}
			}
			break;
			case 'kind': $html = $embeddedAsset->type ? Craft::t('embeddedassets', ucfirst($embeddedAsset->type)) : null; break;
			case 'width': $html = $embeddedAsset->imageWidth ? $embeddedAsset->imageWidth . 'px' : null; break;
			case 'height': $html = $embeddedAsset->imageHeight ? $embeddedAsset->imageHeight . 'px' : null; break;
			case 'imageSize':
			{
				$width = $embeddedAsset->imageWidth;
				$height = $embeddedAsset->imageHeight;
				$html = $width && $height ? "$width × $height" : null;
			}
			break;
			case 'link':
			{
				if ($embeddedAsset->url)
				{
					$linkTitle = Craft::t('app', 'Visit webpage');
					$html = "<a href='$embeddedAsset->url' target='_blank' rel='noopener' data-icon='world' title='$linkTitle'></a>";
				}
			}
			break;
		}

		return $html;
	}
}
