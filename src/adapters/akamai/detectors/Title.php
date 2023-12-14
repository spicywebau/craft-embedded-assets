<?php

namespace spicyweb\embeddedassets\adapters\akamai\detectors;

use Embed\Detectors\Title as BaseTitleDetector;

/**
 * Embed title detector class for Akamai.
 *
 * @package spicyweb\embeddedassets\adapters\akamai\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Title extends BaseTitleDetector
{
    public function detect(): string
    {
        // Embed data for Vimeo is incorrectly resolving some URLs to inaccessible streaming URLs
        $url = $this->extractor->getRequest()->getUri();
        return preg_match('/^https:\/\/player\.vimeo\.com\/(external|progressive_redirect)/', $url)
            ? $url
            : parent::detect();
    }
}
