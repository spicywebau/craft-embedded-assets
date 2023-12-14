<?php

namespace spicyweb\embeddedassets\adapters\akamai;

use Embed\Extractor as BaseExtractor;
use spicyweb\embeddedassets\adapters\akamai\detectors\Title;
use spicyweb\embeddedassets\adapters\akamai\detectors\Url;

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
            'title' => new Title($this),
            'url' => new Url($this),
        ];
    }
}
