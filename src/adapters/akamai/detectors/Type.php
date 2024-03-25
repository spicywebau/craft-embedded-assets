<?php

namespace spicyweb\embeddedassets\adapters\akamai\detectors;

use spicyweb\embeddedassets\adapters\default\detectors\Type as BaseType;

/**
 * Embed type detector class for Akamai.
 *
 * @package spicyweb\embeddedassets\adapters\akamai\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Type extends BaseType
{
    public function detect(): ?string
    {
        return $this->extractor->isVimeo()
            ? 'video'
            : parent::detect();
    }
}
