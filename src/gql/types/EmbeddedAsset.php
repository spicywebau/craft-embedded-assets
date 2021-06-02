<?php

namespace spicyweb\embeddedassets\gql\types;

use craft\gql\types\elements\Element;
use GraphQL\Type\Definition\ResolveInfo;
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

    /**
     * @inheritdoc
     * @since 2.7.0
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo)
    {
        return $resolveInfo->fieldName === 'iframeSrc'
            ? $source->{'get' . ucfirst($resolveInfo->fieldName)}($arguments['params'])
            : parent::resolve($source, $arguments, $context, $resolveInfo);
    }
}
