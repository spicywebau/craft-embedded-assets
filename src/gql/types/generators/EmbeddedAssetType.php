<?php

namespace spicyweb\embeddedassets\gql\types\generators;

use craft\gql\base\GeneratorInterface;
use craft\gql\base\ObjectType;
use craft\gql\base\SingleGeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeManager;
use spicyweb\embeddedassets\gql\interfaces\EmbeddedAsset as EmbeddedAssetInterface;
use spicyweb\embeddedassets\gql\types\EmbeddedAsset;

/**
 * Embedded Asset GraphQL type generator for Craft CMS Pro.
 *
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.4.0
 */
class EmbeddedAssetType implements GeneratorInterface, SingleGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public static function generateTypes($context = null): array
    {
        return [static::generateType($context)];
    }

    /**
     * @inheritdoc
     */
    public static function generateType($context): ObjectType
    {
        $typeName = 'EmbeddedAsset';

        return GqlEntityRegistry::getEntity($typeName) ?: GqlEntityRegistry::createEntity($typeName, new EmbeddedAsset([
            'name' => $typeName,
            'fields' => function() use ($typeName) {
                return TypeManager::prepareFieldDefinitions(EmbeddedAssetInterface::getFieldDefinitions(), $typeName);
            }
        ]));
    }
}
