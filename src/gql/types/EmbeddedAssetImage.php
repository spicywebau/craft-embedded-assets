<?php

namespace spicyweb\embeddedassets\gql\types;

use craft\gql\base\ObjectType;
use spicyweb\embeddedassets\gql\interfaces\EmbeddedAssetImage as EmbeddedAssetImageInterface;

/**
 * Embedded Asset GraphQL type for Craft CMS Pro.
 *
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.11.0
 */
class EmbeddedAssetImage extends ObjectType
{
    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        $config['interfaces'] = [EmbeddedAssetImageInterface::getType()];

        parent::__construct($config);
    }
}
