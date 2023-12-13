<?php

namespace spicyweb\embeddedassets\adapters\pbs;

use Embed\Extractor as BaseExtractor;
use spicyweb\embeddedassets\adapters\pbs\detectors\Code;

/**
 * Embed extractor class for PBS websites.
 *
 * @package spicyweb\embeddedassets\adapters\pbs
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Extractor extends BaseExtractor
{
    public function createCustomDetectors(): array
    {
        return [
            'code' => new Code($this),
        ];
    }
}
