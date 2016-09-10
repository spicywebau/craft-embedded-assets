<?php

namespace Craft;

/**
 * Class EmbeddedAssetsPlugin
 *
 * Thank you for using Craft Embedded Assets!
 * @see https://github.com/benjamminf/craft-embedded-assets
 * @package Craft
 */
class EmbeddedAssetsPlugin extends BasePlugin
{
	public function getName()
	{
		return Craft::t('Embedded Assets');
	}

	public function getDescription()
	{
		return 'Add embeddable media such as YouTube videos to your assets manager';
	}

	public function getVersion()
	{
		return '0.3.4';
	}

	public function getSchemaVersion()
	{
		return '0.0.1';
	}

	public function getCraftMinimumVersion()
	{
		return '2.5';
	}

	public function getPHPMinimumVersion()
	{
		return '5.5';
	}

	public function getDeveloper()
	{
		return 'Benjamin Fleming';
	}

	public function getDeveloperUrl()
	{
		return 'http://benjamminf.github.io';
	}

	public function getDocumentationUrl()
	{
		return 'https://github.com/benjamminf/craft-embedded-assets';
	}

	public function getReleaseFeedUrl()
	{
		return 'https://raw.githubusercontent.com/benjamminf/craft-embedded-assets/master/releases.json';
	}

	/**
	 * Shorthand for getting the file name prefix setting.
	 *
	 * @return mixed
	 */
	public static function getFileNamePrefix()
	{
		return craft()->config->get('filenamePrefix', 'embeddedassets');
	}

	/**
	 * Shorthand for getting the whitelist setting.
	 *
	 * @return mixed
	 */
	public static function getWhitelist()
	{
		$plugin = craft()->plugins->getPlugin('embeddedAssets');

		return $plugin->getSettings()->whitelist;
	}

	/**
	 * Shorthand for getting the parameters setting.
	 *
	 * @return mixed
	 */
	public static function getParameters()
	{
		$plugin = craft()->plugins->getPlugin('embeddedAssets');

		return $plugin->getSettings()->parameters;
	}

	/**
	 * @return string
	 */
	public static function getCacheKey()
	{
		return 'embeddedassets-0.3.4_thumbs';
	}

	/**
	 * Loads all dependencies for this plugin.
	 * The plugin currently depends on the Essence library.
	 *
	 * @see https://github.com/essence/essence
	 */
	public static function loadDependencies()
	{
		require CRAFT_PLUGINS_PATH . '/embeddedassets/vendor/autoload.php';
	}

	/**
	 * Initialise the plugin by loading dependencies and resources.
	 */
	public function init()
	{
		parent::init();

		if(craft()->request->isCpRequest() && $this->isCompatible())
		{
			$this->includeResources();
		}
	}

	/**
	 * Checks if the plugin is compatible before installation.
	 *
	 * @return bool
	 */
	public function onBeforeInstall()
	{
		return $this->isCompatible();
	}

	/**
	 * Returns if the plugin is compatible with the current system stack.
	 *
	 * @return bool
	 */
	public function isCompatible()
	{
		return $this->isCraftRequiredVersion() && $this->isPHPRequiredVersion();
	}

	/**
	 * Defines the earliest version of Craft that this plugin is compatible with.
	 *
	 * @return boolean - Whether the current installed version of Craft is compatible
	 */
	public function isCraftRequiredVersion()
	{
		return version_compare(craft()->getVersion(), $this->getCraftMinimumVersion(), '>=');
	}

	/**
	 * Defines the earliest version of PHP that this plugin is compatible with.
	 *
	 * @return boolean - Whether the current version of PHP is compatible
	 */
	public function isPHPRequiredVersion()
	{
		return version_compare(PHP_VERSION, $this->getPHPMinimumVersion(), '>=');
	}

	/**
	 * The default settings for the plugin.
	 *
	 * @setting whitelist - This is a list of domains that will be preserved when purifying the HTML
	 * @setting parameters - Extra `GET` parameters to be supplied when requesting media
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'whitelist' => array(AttributeType::Mixed, 'default' => array(
				'23hq.com',
				'app.net',
				'animoto.com',
				'aol.com',
				'collegehumor.com',
				'dailymotion.com',
				'deviantart.com',
				'embed.ly',
				'fav.me',
				'flic.kr',
				'flickr.com',
				'funnyordie.com',
				'hulu.com',
				'imgur.com',
				'instagr.am',
				'instagram.com',
				'kickstarter.com',
				'meetup.com',
				'meetup.ps',
				'nfb.ca',
				'official.fm',
				'rdio.com',
				'soundcloud.com',
				'twitter.com',
				'vimeo.com',
				'vine.co',
				'wikipedia.org',
				'wikimedia.org',
				'wordpress.com',
				'youtu.be',
				'youtube.com',
				'youtube-nocookie.com',
			)),
			'parameters' => array(AttributeType::Mixed, 'default' => array(
				'maxwidth' => 1280,
				'maxheight' => 960,
			)),
		);
	}

	/**
	 * Formats values from the CP settings form to be saved into the DB.
	 *
	 * @param array $postSettings
	 * @return array
	 */
	public function prepSettings($postSettings)
	{
		$settings = array(
			'whitelist' => array_map(function($domain)
			{
				return trim($domain);
			}, explode(PHP_EOL, $postSettings['whitelist'])),
			'parameters' => array()
		);

		foreach($postSettings['parameters'] as $parameter)
		{
			$param = trim($parameter['param']);
			$value = trim($parameter['value']);

			$settings['parameters'][$param] = $value;
		}

		return $settings;
	}

	/**
	 * Renders the plugin's settings page.
	 *
	 * @return mixed
	 */
	public function getSettingsHtml()
	{
		return craft()->templates->render('embeddedassets/settings', array(
			'settings' => $this->getSettings()
		));
	}

	/**
	 * Hook for adding additional table attributes to the Assets manager.
	 * Adds a provider attribute for embedded assets to display their providers.
	 *
	 * @return array
	 */
	public function defineAdditionalAssetTableAttributes()
	{
		return array(
			'provider' => array('label' => Craft::t('Provider')),
			'author' => array('label' => Craft::t('Author')),
		);
	}

	/**
	 * Hook for formatting the HTML for embedded asset attributes in the Assets manager.
	 *
	 * @param $element
	 * @param $attribute
	 * @return null|string
	 */
	public function getAssetTableAttributeHtml($element, $attribute)
	{
		if($element instanceof AssetFileModel)
		{
			$embed = craft()->embeddedAssets->getEmbeddedAsset($element);

			if($embed)
			{
				switch($attribute)
				{
					case 'filename':
					{
						$stripProtocol = preg_replace('/^((https?:\/\/)|(\/\/))/', '', $embed->url);
						$stripWWW = preg_replace('/^www\./', '', $stripProtocol);
						$trimmedUrl = mb_strimwidth($stripWWW, 0, 50, '...');

						return HtmlHelper::encodeParams(
							'<a href="{url}" target="_blank" style="word-break: break-word;">{name}</a>',
							array(
								'url' => $embed->url,
								'name' => $trimmedUrl,
							)
						);
					}

					case 'provider':
					{
						return HtmlHelper::encodeParams(
							'<a href="{url}" target="_blank" data-provider="{data}">{name}</a>',
							array(
								'url' => $embed->providerUrl,
								'data' => StringHelper::toCamelCase($embed->providerName),
								'name' => $embed->providerName,
							)
						);
					}

					case 'author':
					{
						return HtmlHelper::encodeParams(
							'<a href="{url}" target="_blank">{name}</a>',
							array(
								'url' => $embed->authorUrl,
								'name' => $embed->authorName,
							)
						);
					}

					case 'size':
					{
						return '';
					}

					case 'kind':
					{
						switch($embed->type)
						{
							case 'photo': return Craft::t("Embedded Image");
							case 'video': return Craft::t("Embedded Video");
							case 'link':  return Craft::t("Embedded Link");
						}

						return Craft::t("Embedded Media");
					}

					case 'imageSize':
					{
						if($embed->type == 'image')
						{
							$width = $embed->width;
							$height = $embed->height;

							if($width && $height)
							{
								return $width . ' &times; ' . $height;
							}
							else
							{
								return null;
							}
						}
						else
						{
							return '';
						}
					}

					case 'width':
					case 'height':
					{
						if($embed->type == 'image')
						{
							$size = $embed->$attribute;

							return ($size ? $size . 'px' : null);
						}
						else
						{
							return '';
						}
					}
				}
			}
		}

		switch($attribute)
		{
			case 'provider':
			case 'author':
				return '';
		}

		return null;
	}

	/**
	 * Loads all CSS and JS resources for the plugin.
	 */
	protected function includeResources()
	{
		if(!craft()->request->isAjaxRequest())
		{
			craft()->templates->includeCssResource('embeddedassets/css/main.css');
			craft()->templates->includeJsResource('embeddedassets/js/EmbeddedAssets.js');
			craft()->templates->includeJsResource('embeddedassets/js/EmbeddedIndex.js');
			craft()->templates->includeJsResource('embeddedassets/js/EmbeddedInput.js');
			craft()->templates->includeJsResource('embeddedassets/js/EmbedModal.js');
			craft()->templates->includeJs('window.EmbeddedAssets.thumbnails=' . JsonHelper::encode($this->_getThumbnails()));
		}
	}

	/**
	 * Returns an array of all embedded assets thumbnails, indexed by the asset file models ID.
	 * This method is used to inject the asset thumbnails into the CP front-end. Since embedded asset files are stored
	 * as JSON files, there's no supported way of setting the thumbnail on the front-end for these files. The
	 * alternative is to pass a list of these thumbnails to the front-end, and use JS to patch them on-top of the
	 * elements system.
	 *
	 * @return array
	 */
	private function _getThumbnails()
	{
		$cacheKey = self::getCacheKey();
		$cache = craft()->cache->get($cacheKey);

		if(!$cache)
		{
			
			$prefix = self::getFileNamePrefix();
			$assets = craft()->elements->getCriteria(ElementType::Asset, array(
				'kind' => 'json',
				'filename' => $prefix.'*',
				'limit' => null,
			))->find();

			$thumbnails = array();

			foreach($assets as $asset)
			{
				$embed = craft()->embeddedAssets->getEmbeddedAsset($asset);

				if($embed)
				{
					$thumbnails[$asset->id] = $embed->thumbnailUrl;
				}
			}

			craft()->cache->set($cacheKey, $thumbnails);
			$cache = $thumbnails;
		}

		return $cache;
	}
}
