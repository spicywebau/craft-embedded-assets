<?php

namespace spicyweb\embeddedassets\gql\types\generators;

use Craft;
use craft\gql\base\GeneratorInterface;
use craft\gql\base\SingleGeneratorInterface;
use craft\gql\GqlEntityRegistry;
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
    public static function generateTypes(mixed $context = null): array
    {
        return [static::generateType($context)];
    }

    /**
     * @inheritdoc
     */
    public static function generateType(mixed $context): mixed
    {
        $typeName = 'EmbeddedAsset';

        return GqlEntityRegistry::getEntity($typeName) ?: GqlEntityRegistry::createEntity($typeName, new EmbeddedAsset([
            'name' => $typeName,
            'fields' => function() use ($typeName) {
                return Craft::$app->getGql()->prepareFieldDefinitions(EmbeddedAssetInterface::getFieldDefinitions(), $typeName);
            },
        ]));
    }
}
