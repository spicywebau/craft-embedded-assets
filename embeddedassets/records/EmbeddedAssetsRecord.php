<?php

namespace Craft;

class EmbeddedAssetsRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'embeddedassets';
	}

	public function defineRelations()
	{
		return array(
			'asset' => array(static::BELONGS_TO, 'AssetFileRecord', 'onDelete' => static::CASCADE),
		);
	}

	protected function defineAttributes()
	{
		return array(
			'type'            => AttributeType::Number,
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
		);
	}
}
