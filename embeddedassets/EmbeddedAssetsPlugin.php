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
}
