<?php

namespace spicyweb\embeddedassets\gql\types;

use craft\gql\types\elements\Element;
use spicyweb\embeddedassets\gql\interfaces\EmbeddedAsset as EmbeddedAssetInterface;

/**
 * Embedded Asset GraphQL type for Craft CMS Pro.
 *
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.4.0
 */
class EmbeddedAsset extends Element
{
    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        $config['interfaces'] = [EmbeddedAssetInterface::getType()];

        parent::__construct($config);
    }
}
