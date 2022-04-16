<?php

namespace spicyweb\embeddedassets\gql\resolvers;

use craft\elements\Asset as AssetElement;
use craft\gql\resolvers\elements\Asset as AssetResolver;
use craft\helpers\Gql as GqlHelper;
use GraphQL\Type\Definition\ResolveInfo;
use spicyweb\embeddedassets\Plugin as EmbeddedAssets;

/**
 * Embedded Asset GraphQL resolver for Craft CMS Pro.
 *
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.4.0
 */
class EmbeddedAsset extends AssetResolver
{
    /**
     * @inheritdoc
     */
    public static function resolveOne($source, array $arguments, $context, ResolveInfo $resolveInfo): mixed
    {
        if (!($source instanceof AssetElement)) {
            return null;
        }

        $value = EmbeddedAssets::$plugin->methods->getEmbeddedAsset($source);

        return $value !== null ? GqlHelper::applyDirectives($source, $resolveInfo, $value) : null;
    }
}
