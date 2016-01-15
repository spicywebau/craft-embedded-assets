<?php

namespace Craft;

class EmbeddedAssetsModel extends BaseComponentModel
{
	protected function defineAttributes()
	{
		return array_merge(parent::defineAttributes(), array(
			'assetId'         => AttributeType::Number,
			'type'            => AttributeType::String,
			'version'         => AttributeType::String,
			'url'             => AttributeType::String,
			'title'           => AttributeType::String,
			'description'     => AttributeType::String,
			'authorName'      => AttributeType::String,
			'authorUrl'       => AttributeType::String,
			'providerName'    => AttributeType::String,
			'providerUrl'     => AttributeType::String,
			'cacheAge'        => AttributeType::String,
			'thumbnailUrl'    => AttributeType::String,
			'thumbnailWidth'  => AttributeType::Number,
			'thumbnailHeight' => AttributeType::Number,
			'html'            => AttributeType::String,
			'width'           => AttributeType::Number,
			'height'          => AttributeType::Number,
		));
	}
}
