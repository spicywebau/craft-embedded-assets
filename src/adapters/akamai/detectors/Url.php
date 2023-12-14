<?php

namespace spicyweb\embeddedassets\adapters\akamai\detectors;

use Embed\Detectors\Url as BaseUrlDetector;
use Psr\Http\Message\UriInterface;

/**
 * Embed URL detector class for Akamai.
 *
 * @package spicyweb\embeddedassets\adapters\akamai\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Url extends BaseUrlDetector
{
    public function detect(): UriInterface
    {
        // Embed data for Vimeo is incorrectly resolving some URLs to inaccessible streaming URLs
        $url = $this->extractor->getRequest()->getUri();
        return preg_match('/^https:\/\/player\.vimeo\.com\/(external|progressive_redirect)/', $url)
            ? $url
            : parent::detect();
    }
}
