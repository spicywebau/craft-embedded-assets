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
			craft()->templates->includeJsResource('embeddedassets/js/EmbedModal.js');
		}
	}

	public static function getFileNamePrefix()
	{
		return 'embed_';
	}

	/*
	public function modifyAssetSources($sources, $context)
	{

	}

	public function defineAdditionalAssetTableAttributes($attributes)
	{

	}
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
						return HtmlHelper::encodeParams(
							'<a href="{url}" target="_blank" style="word-break: break-word;">{name}</a>',
							array(
								'url' => $embed->url,
								'name' => mb_strimwidth($embed->url, 0, 50, '...'),
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

		return null;
	}
}
