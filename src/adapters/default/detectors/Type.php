<?php

namespace spicyweb\embeddedassets\adapters\default\detectors;

use Embed\Detectors\Detector;

/**
 * Default Embed type detector class for Embedded Assets.
 *
 * @package spicyweb\embeddedassets\adapters\default\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Type extends Detector
{
    public function detect(): ?string
    {
        return $this->extractor->getOEmbed()->str('type') ?: 'link';
    }
}
