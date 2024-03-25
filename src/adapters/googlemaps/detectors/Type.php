<?php

namespace spicyweb\embeddedassets\adapters\googlemaps\detectors;

use Embed\Detectors\Detector;

/**
 * Embed type detector class for Google Maps.
 *
 * @package spicyweb\embeddedassets\adapters\googlemaps\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Type extends Detector
{
    public function detect(): ?string
    {
        return 'rich';
    }
}
