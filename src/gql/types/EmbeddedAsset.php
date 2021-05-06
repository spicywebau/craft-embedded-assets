<?php

namespace spicyweb\embeddedassets\gql\types;

use Craft;
use craft\gql\types\elements\Element;
use craft\helpers\Gql;
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
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo)
    {
        /* @var EmbeddedAsset $source */
        $fieldName = $resolveInfo->fieldName;

        if ($fieldName === 'url') {
            if($arguments['asVideoUrl']) {
                return $source->getVideoUrl($arguments['params'] ?? []);
            }
        }

        return parent::resolve($source, $arguments, $context, $resolveInfo);
    }
}
