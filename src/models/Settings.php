<?php
namespace benf\embeddedassets\models;

use craft\base\Model;

class Settings extends Model
{
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

	public $parameters = [
		'maxwidth' => '1920',
		'maxheight' => '1080',
	];

	public function rules()
	{
		return [
			['whitelist', 'each', 'rule' => ['string']],
			['parameters', 'each', 'rule' => ['string']],
		];
	}
}
