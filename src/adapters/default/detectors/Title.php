<?php

namespace spicyweb\embeddedassets\adapters\default\detectors;

use Embed\Detectors\Title as BaseTitleDetector;

/**
 * Default Embed title detector class for Embedded Assets.
 *
 * @package spicyweb\embeddedassets\adapters\default\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Title extends BaseTitleDetector
{
    public function detect(): ?string
    {
        // Fall back to the URL if the title is null
        return parent::detect() ?? $this->extractor->getRequest()->getUri();
    }
}
