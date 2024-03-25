<?php

namespace spicyweb\embeddedassets\adapters\default;

use Embed\Extractor as BaseExtractor;
use spicyweb\embeddedassets\adapters\default\detectors\Title;
use spicyweb\embeddedassets\adapters\default\detectors\Type;

/**
 * Default Embed extractor class for Embedded Assets.
 *
 * @package spicyweb\embeddedassets\adapters\default
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Extractor extends BaseExtractor
{
    public function createCustomDetectors(): array
    {
        return [
            'title' => new Title($this),
            'type' => new Type($this),
        ];
    }
}
