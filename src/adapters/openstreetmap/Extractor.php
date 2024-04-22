<?php

namespace spicyweb\embeddedassets\adapters\openstreetmap;

use spicyweb\embeddedassets\adapters\default\Extractor as BaseExtractor;
use spicyweb\embeddedassets\adapters\openstreetmap\detectors\Code;
use spicyweb\embeddedassets\adapters\openstreetmap\detectors\Type;

/**
 * Embed extractor class for OpenStreetMap.
 *
 * @package spicyweb\embeddedassets\adapters\openstreetmap
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.1.0
 */
class Extractor extends BaseExtractor
{
    public function createCustomDetectors(): array
    {
        return [
            'code' => new Code($this),
            'type' => new Type($this),
        ] + parent::createCustomDetectors();
    }
}
