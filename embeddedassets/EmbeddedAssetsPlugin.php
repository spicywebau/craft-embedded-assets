<?php

namespace Craft;

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
		return '0.0.1';
	}

	public function getSchemaVersion()
	{
		return '0.0.1';
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

	public function init()
	{
		parent::init();

		if(craft()->request->isCpRequest() && $this->isCraftRequiredVersion())
		{
			$this->loadDependencies();
			$this->includeResources();
		}
	}

	protected function defineSettings()
	{
		return array(
			'filenamePrefix' => array(AttributeType::String, 'default' => 'embed_'),
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
			))
		);
	}

	public function prepSettings($settings)
	{
		// Modify $settings here...

		return $settings;
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('embeddedassets/settings', array(
			'settings' => $this->getSettings()
		));
	}

	public function isCraftRequiredVersion()
	{
		return version_compare(craft()->getVersion(), '2.5', '>=');
	}

	protected function loadDependencies()
	{
		require CRAFT_PLUGINS_PATH . '/embeddedassets/vendor/autoload.php';
	}

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

	private function _getThumbnails()
	{
		// Escape for using in LIKE clause
		// See: http://www.yiiframework.com/doc/guide/1.1/en/database.query-builder
		$prefix = strtr(self::getFileNamePrefix(), array('%' => '\%', '_' => '\_'));
		$results = craft()->db->createCommand()
			->select('assetfiles.*')
			->from('assetfiles assetfiles')
			->where(array(
				'like',
				'assetfiles.filename',
				$prefix . '%.json'
			))
			->queryAll();

		$assets = AssetFileModel::populateModels($results, 'id');
		$thumbnails = array();

		foreach($assets as $id => $asset)
		{
			$embed = craft()->embeddedAssets->getEmbeddedAsset($asset);

			if($embed)
			{
				$thumbnails[$id] = $embed->thumbnailUrl;
			}
		}

		return $thumbnails;
	}

	public static function getFileNamePrefix()
	{
		return 'embed_';
	}

	public static function getWhitelist()
	{
		return array(
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
			'twitter.com',
			'vimeo.com',
			'vine.co',
			'wikipedia.org',
			'wikimedia.org',
			'wordpress.com',
			'youtu.be',
			'youtube.com',
			'youtube-nocookie.com',
		);
	}

	public static function getParameters()
	{
		return array(
			'maxwidth' => 800,
			'maxheight' => 600,
		);
	}

	public function defineAdditionalAssetTableAttributes()
	{
		return array(
			'provider' => array('label' => Craft::t('Provider')),
		);
	}

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
						return HtmlHelper::encodeParams(
							'<a href="{url}" target="_blank" style="word-break: break-word;">{name}</a>',
							array(
								'url' => $embed->url,
								'name' => mb_strimwidth($embed->url, 0, 50, '...'),
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

					case 'size':
					{
						return '';
					}

					case 'kind':
					{
						$kind = IOHelper::getFileKindLabel($embed->type);

						return 'Embedded ' . ($kind ? $kind : 'Media');
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

		if($attribute === 'provider')
		{
			return '';
		}

		return null;
	}
}
