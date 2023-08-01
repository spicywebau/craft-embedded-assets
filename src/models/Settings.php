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
    public string $embedlyKey = '';
    
    /**
     * @var string
     */
    public string $iframelyKey = '';
    
    /**
     * @var string
     */
    public string $googleKey = '';
    
    /**
     * @var string
     */
    public string $soundcloudKey = '';
    
    /**
     * @var string
     */
    public string $facebookKey = '';

    /**
     * @var string
     */
    public string $referer = '';
    
    /**
     * @var array of parameters
     */
    public array $parameters = [
        ['param' => 'maxwidth', 'value' => '1920'],
        ['param' => 'maxheight', 'value' => '1080'],
    ];
    
    /**
     * @var array of strings
     */
    public array $whitelist = [
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

        // Wistia
        'wistia.com',
        'wistia.net',

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
    public array $extraWhitelist = [];
    
    /**
     * @var int
     */
    public int $minImageSize = 16;
    
    /**
     * @var int
     */
    public int $maxAssetNameLength = 50;
    
    /**
     * @var int
     */
    public int $maxFileNameLength = 50;
    
    /**
     * @var int
     */
    public int $cacheDuration = 5 * 60; // 5 minutes
    
    /**
     * @var bool
     */
    public bool $showThumbnailsInCp = true;

    /**
     * @var bool
     * @since 2.6.0
     */
    public bool $useYouTubeNoCookie = false;

    /**
     * @var bool
     * @since 2.6.0
     */
    public bool $disableVimeoTracking = false;

    /**
     * @var bool
     * @since 3.0.2
     */
    public bool $enableAutoRefresh = true;

    /**
     * @var bool
     * @since 3.1.0
     */
    public bool $preventNonWhitelistedUploads = false;

    /**
     * @inheritdoc
     */
    protected function defineBehaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['embedlyKey', 'iframelyKey', 'googleKey', 'soundcloudKey', 'facebookKey', 'referer'],
            ],
        ];
    }
    
    /**
     * @return array
     */
    protected function defineRules(): array
    {
        return [
            [['embedlyKey', 'iframelyKey', 'googleKey', 'soundcloudKey', 'facebookKey', 'referer'], StringValidator::class],
            ['parameters', 'each', 'rule' => [ParameterValidator::class]],
            [['whitelist', 'extraWhitelist'], 'each', 'rule' => [StringValidator::class]],
            [['maxAssetNameLength', 'maxFileNameLength'], 'integer', 'min' => 10],
            ['cacheDuration', 'integer', 'min' => 0],
            [[
                'disableVimeoTracking',
                'enableAutoRefresh',
                'preventNonWhitelistedUploads',
                'showThumbnailsInCp',
                'useYouTubeNoCookie',
            ], 'boolean'],
        ];
    }
}
