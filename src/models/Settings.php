<?php

namespace spicyweb\embeddedassets\models;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\validators\StringValidator;

use spicyweb\embeddedassets\validators\Parameter as ParameterValidator;

/**
 * Class Settings
 *
 * @package spicyweb\embeddedassets\models
 * @author Spicy Web <craft@spicyweb.com.au>
 * @author Benjamin Fleming
 * @since 1.0.0
 */
class Settings extends Model
{
    /**
     * @var string
     */
    public $embedlyKey = '';
    
    /**
     * @var string
     */
    public $iframelyKey = '';
    
    /**
     * @var string
     */
    public $googleKey = '';
    
    /**
     * @var string
     */
    public $soundcloudKey = '';
    
    /**
     * @var string
     */
    public $facebookKey = '';

    /**
     * @var string
     */
    public $referer = '';
    
    /**
     * @var string
     */
    public $customAdaptersNamespace = '';

    /**
     * @var array of parameters
     */
    public $parameters = [
        ['param' => 'maxwidth', 'value' => '1920'],
        ['param' => 'maxheight', 'value' => '1080'],
    ];
    
    /**
     * @var array of strings
     */
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
        'netflix.com',

        // PBS and affiliates
        // TODO: add more affiliates as I find them
        'pbs.org',
        'nhpbs.org',

        // Reddit
        'reddit.com',
        
        // SoundCloud
        'soundcloud.com',

        // TikTok
        'tiktok.com',

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
    
    /**
     * @var array of strings
     */
    public $extraWhitelist = [];
    
    /**
     * @var int
     */
    public $minImageSize = 16;
    
    /**
     * @var int
     */
    public $maxAssetNameLength = 50;
    
    /**
     * @var int
     */
    public $maxFileNameLength = 50;
    
    /**
     * @var int
     */
    public $cacheDuration = 5 * 60; // 5 minutes
    
    /**
     * @var bool
     */
    public $showThumbnailsInCp = true;
    
    public function behaviors()
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['embedlyKey', 'iframelyKey', 'googleKey', 'soundcloudKey', 'facebookKey', 'referer', 'customAdaptersNamespace'],
            ],
        ];
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['embedlyKey', 'iframelyKey', 'googleKey', 'soundcloudKey', 'facebookKey', 'referer', 'customAdaptersNamespace'], StringValidator::class],
            ['parameters', 'each', 'rule' => [ParameterValidator::class]],
            [['whitelist', 'extraWhitelist'], 'each', 'rule' => [StringValidator::class]],
            [['maxAssetNameLength', 'maxFileNameLength'], 'integer', 'min' => 10],
            ['cacheDuration', 'integer', 'min' => 0],
            ['showThumbnailsInCp', 'boolean'],
        ];
    }
}
