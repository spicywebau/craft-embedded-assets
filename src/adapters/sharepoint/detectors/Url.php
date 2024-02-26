<?php

namespace spicyweb\embeddedassets\adapters\sharepoint\detectors;

use Embed\Detectors\Url as BaseUrlDetector;
use Psr\Http\Message\UriInterface;

/**
 * Embed URL detector class for Sharepoint.
 *
 * @package spicyweb\embeddedassets\adapters\sharepoint\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Url extends BaseUrlDetector
{
    public function detect(): UriInterface
    {
        // Embed data for Sharepoint is incorrectly resolving some URLs to inaccessible URLs
        $url = $this->extractor->getRequest()->getUri();
        return preg_match('/^https:\/\/.+\.sharepoint\.com\/:f:\/g/', $url)
            ? $url
            : parent::detect();
    }
}
