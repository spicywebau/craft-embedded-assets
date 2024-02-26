<?php

namespace spicyweb\embeddedassets\adapters\sharepoint;

use spicyweb\embeddedassets\adapters\sharepoint\detectors\Url;
use spicyweb\embeddedassets\adapters\default\Extractor as BaseExtractor;

/**
 * Embed extractor class for Sharepoint.
 *
 * @package spicyweb\embeddedassets\adapters\sharepoint
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
