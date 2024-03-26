<?php

namespace spicyweb\embeddedassets\adapters\akamai;

use spicyweb\embeddedassets\adapters\akamai\detectors\Type;
use spicyweb\embeddedassets\adapters\akamai\detectors\Url;
use spicyweb\embeddedassets\adapters\default\Extractor as BaseExtractor;

/**
 * Embed extractor class for Akamai.
 *
 * @package spicyweb\embeddedassets\adapters\akamai
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Extractor extends BaseExtractor
{
    public function createCustomDetectors(): array
    {
        return [
            'type' => new Type($this),
            'url' => new Url($this),
        ] + parent::createCustomDetectors();
    }

    /**
     * Checks whether this is a Vimeo embed, since embed data for Vimeo is incorrectly resolving
     * some URLs to inaccessible streaming URLs.
     *
     * @return bool
     */
    public function isVimeo(): bool
    {
        return (bool)preg_match(
            '/^https:\/\/player\.vimeo\.com\/(external|progressive_redirect)/',
            $this->getRequest()->getUri(),
        );
    }
}
