<?php

namespace spicyweb\embeddedassets\adapters\pbs\detectors;

use spicyweb\embeddedassets\adapters\default\detectors\Type as BaseType;

/**
 * Embed type detector class for PBS websites.
 *
 * @package spicyweb\embeddedassets\adapters\default\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Type extends BaseType
{
    public function detect(): ?string
    {
        return $this->extractor->getIframe() !== null
            ? 'video'
            : parent::detect();
    }
}
