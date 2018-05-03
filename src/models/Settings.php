<?php
namespace benf\embeddedassets\models;

use craft\base\Model;

class Settings extends Model
{
	public $embedlyKey = '';
	public $iframelyKey = '';
	public $googleKey = '';
	public $soundcloudKey = '';
	public $facebookKey = '';

	public $parameters = [
		['param' => 'maxwidth', 'value' => '1920'],
		['param' => 'maxheight', 'value' => '1080'],
	];

	public function rules()
	{
		return [
			['embedlyKey', 'string'],
			['iframelyKey', 'string'],
			['googleKey', 'string'],
			['soundcloudKey', 'string'],
			['facebookKey', 'string'],
			['parameters', function($attributeName, $ruleParameters, $validator)
			{
				$parameters = $this->$attributeName;

				if (!is_array($parameters))
				{
					$this->addError($attributeName, "Parameters must be an array.");
				}
				else
				{
					foreach ($parameters as $parameter)
					{
						if (!is_array($parameter))
						{
							$this->addError($attributeName, "Parameter must be an array.");
						}
						else
						{
							if (!isset($parameter['param'])) $this->addError($attributeName, "Parameter must contain a `param` key.");
							elseif (!is_string($parameter['param'])) $this->addError($attributeName, "Parameter name must be a string.");
							elseif (empty($parameter['param'])) $this->addError($attributeName, "Parameter name is required.");

							if (!isset($parameter['value'])) $this->addError($attributeName, "Parameter must contain a `value` key.");
							elseif (!is_string($parameter['value'])) $this->addError($attributeName, "Parameter value must be a string.");
							elseif (empty($parameter['value'])) $this->addError($attributeName, "Parameter value is required.");
						}
					}
				}
			}],
		];
	}
}
