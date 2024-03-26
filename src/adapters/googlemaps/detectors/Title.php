<?php

namespace spicyweb\embeddedassets\adapters\googlemaps\detectors;

use function Embed\getDirectory;
use spicyweb\embeddedassets\adapters\default\detectors\Title as BaseTitleDetector;

/**
 * Embed title detector class for Google Maps.
 *
 * @package spicyweb\embeddedassets\adapters\default\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Title extends BaseTitleDetector
{
    public function detect(): ?string
    {
        $path = $this->extractor->getRequest()->getUri()->getPath();

        return match ($this->extractor->getMode()) {
            'place' => urldecode(getDirectory($path, 2)),
            'dir' => urldecode(getDirectory($path, 2)) . ' / ' . urldecode(getDirectory($path, 3)),
            default => parent::detect(),
        };
    }
}
