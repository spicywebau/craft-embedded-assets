<?php

namespace Craft;

class EmbeddedAssetsModel extends BaseComponentModel
{
	public static function populateFromAsset(AssetFileModel $asset)
	{
		if($asset->kind === 'json' && strpos($asset->filename, EmbeddedAssetsPlugin::getFileNamePrefix(), 0) === 0)
		{
			try
			{
				$rawData = craft()->embeddedAssets->readAssetFile($asset);

				if($rawData)
				{
					$data = JsonHelper::decode($rawData);

					if($data['__embeddedasset__'])
					{
						unset($data['__embeddedasset__']);

						$embed = new EmbeddedAssetsModel();
						$embed->id = $asset->id;

						foreach($embed->attributeNames() as $key)
						{
							if(isset($data[$key]))
							{
								switch($key)
								{
									case 'url':
									case 'requestUrl':
									case 'authorUrl':
									case 'providerUrl':
									case 'thumbnailUrl':
									{
										if(UrlHelper::isAbsoluteUrl($data[$key]))
										{
											$embed->$key = $data[$key];
										}
										else
										{
											$embed->$key = UrlHelper::getSiteUrl($data[$key]);
										}
									}
									break;
									default:
									{
										$embed->$key = $data[$key];
									}
								}
							}
						}

						// For embedded assets saved with version 0.2.1 or below, this will provide a usable fallback
						if(empty($embed->requestUrl))
						{
							$embed->requestUrl = $embed->url;
						}

						return $embed;
					}
				}
			}
			catch(\Exception $e)
			{
				EmbeddedAssetsPlugin::log("Error reading embedded asset data on asset {$asset->id} (\"{$e->getMessage()}\")", LogLevel::Error);

				return null;
			}
		}

		return null;
	}

	public function getTitle()
	{
		$checkOrder = array(
			$this->title,
			$this->providerName,
			$this->description,
			$this->authorName,
		);

		foreach($checkOrder as $property)
		{
			if(!empty($property))
			{
				return $property;
			}
		}

		return $this->url;
	}

	protected function defineAttributes()
	{
		return array_merge(parent::defineAttributes(), array(
			'type'            => AttributeType::String,
			'url'             => AttributeType::String,
			'requestUrl'      => AttributeType::String,
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
			'safeHtml'        => AttributeType::String,
			'width'           => AttributeType::Number,
			'height'          => AttributeType::Number,
		));
	}
}
