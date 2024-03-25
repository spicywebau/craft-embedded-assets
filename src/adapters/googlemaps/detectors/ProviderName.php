<?php

namespace spicyweb\embeddedassets\adapters\googlemaps\detectors;

use Embed\Detectors\ProviderName as BaseProviderNameDetector;

/**
 * Embed provider name detector class for Google Maps.
 *
 * @package spicyweb\embeddedassets\adapters\default\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class ProviderName extends BaseProviderNameDetector
{
    public function detect(): string
    {
        return 'Google Maps';
    }
}
