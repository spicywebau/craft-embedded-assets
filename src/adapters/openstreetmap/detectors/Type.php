<?php

namespace spicyweb\embeddedassets\adapters\openstreetmap\detectors;

use spicyweb\embeddedassets\adapters\default\detectors\Type as BaseType;

/**
 * Embed type detector class for OpenStreetMap.
 *
 * @package spicyweb\embeddedassets\adapters\openstreetmap\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.1.0
 */
class Type extends BaseType
{
    public function detect(): ?string
    {
        $requestFragment = $this->extractor->getRequest()->getUri()->getFragment();

        if (!$requestFragment || !preg_match(Code::$requestFragmentMapPattern, $requestFragment, $matches)) {
            return parent::detect();
        }

        return 'rich';
    }
}
