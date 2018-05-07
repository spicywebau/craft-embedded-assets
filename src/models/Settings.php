<?php
namespace benf\embeddedassets\models;

use craft\base\Model;
use craft\validators\StringValidator;

use benf\embeddedassets\validators\Parameter as ParameterValidator;

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
		// Amazon
		'amazon.*',
		'amazon.co.*',

		// amCharts
		'amcharts.com',

		// Baidu
		'baidu.com',

		// CollegeHumor
		'collegehumor.com',

		// Dailymotion
		'dailymotion.com',

		// DeviantART
		'deviantart.com',

		// embed.ly
		'embed.ly',

		// Facebook
		'facebook.*',
		'facebook.co.*',
		'facebook.com.*',
		'fb.com',

		// Google
		'google.*',
		'google.co.*',
		'google.com.*',

		// Hulu
		'hulu.com',

		// Iframely
		'iframely.com',

		// Imgur
		'imgur.com',

		// Instagram
		'instagr.am',
		'instagram.com',

		// Kickstarter
		'kickstarter.com',

		// Meetup
		'meetup.com',
		'meetupstatic.com',

		// Netflix
		'netfix.com',

		// Reddit
		'reddit.com',

		// SoundCloud
		'soundcloud.com',

		// Twitter
		't.co',
		'twitter.*',
		'twitter.co.*',
		'twitter.com.*',
		'twimg.com',

		// Vimeo
		'vimeo.com',

		// Vine
		'vine.co',

		// Wikipedia
		'wikipedia.org',
		'wikimedia.org',

		// WordPress
		'wordpress.com',

		// Yahoo!
		'yahoo.com',

		// YouTube
		'youtu.be',
		'youtube.com',
		'youtube-nocookie.com',
	];

	public $minImageSize = 16;
	public $maxAssetNameLength = 50;
	public $maxFileNameLength = 50;
	public $cacheDuration = 5 * 60; // 5 minutes

	public function rules()
	{
		return [
			['embedlyKey', StringValidator::class],
			['iframelyKey', StringValidator::class],
			['googleKey', StringValidator::class],
			['soundcloudKey', StringValidator::class],
			['facebookKey', StringValidator::class],
			['parameters', 'each', 'rule' => [ParameterValidator::class]],
			['whitelist', 'each', 'rule' => [StringValidator::class]],
			['maxAssetNameLength', 'integer', 'min' => 10],
			['maxFileNameLength', 'integer', 'min' => 10],
			['cacheDuration', 'integer', 'min' => 0],
		];
	}
}
