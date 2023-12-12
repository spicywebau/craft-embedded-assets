<?php

namespace spicyweb\embeddedassets\gql\types;

use craft\gql\base\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use spicyweb\embeddedassets\gql\interfaces\EmbeddedAsset as EmbeddedAssetInterface;

/**
 * Embedded Asset GraphQL type for Craft CMS Pro.
 *
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.4.0
 */
class EmbeddedAsset extends ObjectType
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
    protected function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        return match ($resolveInfo->fieldName) {
            'iframeCode' => $source->getIframeCode(
                $arguments['params'] ?? [],
                $arguments['attributes'] ?? [],
                $arguments['removeAttributes'] ?? [],
            ),
            'iframeSrc' => $source->getIframeSrc($arguments['params']),
            default => parent::resolve($source, $arguments, $context, $resolveInfo),
        };
    }
}
