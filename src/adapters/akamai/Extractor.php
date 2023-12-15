<?php

namespace spicyweb\embeddedassets\adapters\akamai;

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
            'url' => new Url($this),
        ] + parent::createCustomDetectors();
    }
}
