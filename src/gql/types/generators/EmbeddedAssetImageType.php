<?php

namespace spicyweb\embeddedassets\gql\types\generators;

use Craft;
use craft\gql\base\GeneratorInterface;
use craft\gql\base\ObjectType;
use craft\gql\base\SingleGeneratorInterface;
use craft\gql\GqlEntityRegistry;
use spicyweb\embeddedassets\gql\interfaces\EmbeddedAssetImage as EmbeddedAssetImageInterface;
use spicyweb\embeddedassets\gql\types\EmbeddedAssetImage;

/**
 * Embedded Asset image GraphQL type generator for Craft CMS Pro.
 *
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.11.0
 */
class EmbeddedAssetImageType implements GeneratorInterface, SingleGeneratorInterface
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
        $typeName = 'EmbeddedAssetImage';

        return GqlEntityRegistry::getEntity($typeName) ?: GqlEntityRegistry::createEntity($typeName, new EmbeddedAssetImage([
            'name' => $typeName,
            'description' => 'Embedded asset image data',
            'fields' => function() use ($typeName) {
                return Craft::$app->getGql()->prepareFieldDefinitions(EmbeddedAssetImageInterface::getFieldDefinitions(), $typeName);
            },
        ]));
    }
}
