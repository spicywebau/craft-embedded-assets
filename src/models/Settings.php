<?php
namespace benf\embeddedassets\models;

use craft\base\Model;

class Settings extends Model
{
	public $embedlyKey = '';
	public $iframelyKey = '';
	public $googleKey = '';
	public $soundcloudKey = '';
	public $facebookKey = '';

	public $parameters = [
		['param' => 'maxwidth', 'value' => '1920'],
		['param' => 'maxheight', 'value' => '1080'],
	];

	public $whitelist = [
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
	];

	public $maxAssetNameLength = 50;
	public $maxFileNameLength = 50;
	public $cacheDuration = 5 * 60; // 5 minutes

	public function rules()
	{
		return [
			['embedlyKey', 'string'],
			['iframelyKey', 'string'],
			['googleKey', 'string'],
			['soundcloudKey', 'string'],
			['facebookKey', 'string'],
			['parameters', function($attributeName, $ruleParameters, $validator)
			{
				$parameters = $this->$attributeName;

				if (!is_array($parameters))
				{
					$this->addError($attributeName, "Parameters must be an array.");
				}
				else
				{
					foreach ($parameters as $parameter)
					{
						if (!is_array($parameter))
						{
							$this->addError($attributeName, "Parameter must be an array.");
						}
						else
						{
							if (!isset($parameter['param'])) $this->addError($attributeName, "Parameter must contain a `param` key.");
							elseif (!is_string($parameter['param'])) $this->addError($attributeName, "Parameter name must be a string.");
							elseif (empty($parameter['param'])) $this->addError($attributeName, "Parameter name is required.");

							if (!isset($parameter['value'])) $this->addError($attributeName, "Parameter must contain a `value` key.");
							elseif (!is_string($parameter['value'])) $this->addError($attributeName, "Parameter value must be a string.");
							elseif (empty($parameter['value'])) $this->addError($attributeName, "Parameter value is required.");
						}
					}
				}
			}],
			['whitelist', 'each', 'rule' => ['string']],
			['maxAssetNameLength', 'integer', 'min' => 10],
			['maxFileNameLength', 'integer', 'min' => 10],
			['cacheDuration', 'integer', 'min' => 0],
		];
	}
}
