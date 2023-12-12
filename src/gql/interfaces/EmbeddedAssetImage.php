<?php

namespace spicyweb\embeddedassets\gql\interfaces;

use Craft;
use craft\gql\base\InterfaceType;
use craft\gql\GqlEntityRegistry;
use GraphQL\Type\Definition\InterfaceType as GqlInterfaceType;
use GraphQL\Type\Definition\Type;
use spicyweb\embeddedassets\gql\types\generators\EmbeddedAssetImageType;

/**
 * Embedded Asset image GraphQL interface for Craft CMS Pro.
 *
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.11.0
 */
class EmbeddedAssetImage extends InterfaceType
{
    /**
     * @inheritdoc
     */
    public static function getTypeGenerator(): string
    {
        return EmbeddedAssetImageType::class;
    }

    /**
     * @inheritdoc
     */
    public static function getType($fields = null): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::getName())) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(self::getName(), new GqlInterfaceType([
            'name' => static::getName(),
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'This is the interface implemented by all embedded asset images.',
            'resolveType' => function($value) {
                return 'EmbeddedAssetImage';
            },
        ]));

        EmbeddedAssetImageType::generateTypes();

        return $type;
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'EmbeddedAssetImageInterface';
    }

    /**
     * @inheritdoc
     */
    public static function getFieldDefinitions(): array
    {
        return Craft::$app->getGql()->prepareFieldDefinitions([
            'url' => [
                'name' => 'url',
                'type' => Type::string(),
                'description' => 'The URL of the image.',
            ],
            'width' => [
                'name' => 'width',
                'type' => Type::int(),
                'description' => 'The width of the image.',
            ],
            'height' => [
                'name' => 'height',
                'type' => Type::int(),
                'description' => 'The height of the image.',
            ],
            'size' => [
                'name' => 'size',
                'type' => Type::int(),
                'description' => 'The size of the image.',
            ],
            'mime' => [
                'name' => 'mime',
                'type' => Type::string(),
                'description' => 'The MIME type of the image.',
            ],
        ], self::getName());
    }
}
