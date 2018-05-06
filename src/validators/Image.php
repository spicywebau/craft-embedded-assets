<?php
namespace benf\embeddedassets\validators;

use yii\validators\Validator;

use craft\helpers\UrlHelper;

class Image extends Validator
{
	public function validateAttribute($model, $attribute)
	{
		$image = $model->$attribute;

		if (!is_array($image))
		{
			$this->addError($model, $attribute, "Image must be an array.");
		}
		else
		{
			if ($this->validateKeyExists($model, $attribute, 'url', true) && !UrlHelper::isAbsoluteUrl($image['url']))
			{
				$this->addError($model, $attribute, "Image key `url` must be an absolute URL.");
			}

			foreach (['width', 'height', 'size'] as $key)
			{
				if ($this->validateKeyExists($model, $attribute, $key, true) && !is_numeric($image[$key]))
				{
					$this->addError($model, $attribute, "Image key `$key` must be a number.");
				}
			}

			if ($this->validateKeyExists($model, $attribute, 'mime') && !empty($image['mime']) && !is_string($image['mime']))
			{
				$this->addError($model, $attribute, "Image key `mime` must be a string.");
			}
		}
	}

	protected function validateKeyExists($model, $attribute, string $key, bool $required = false): bool
	{
		$array = $model->$attribute;
		$valid = false;

		if (is_array($array))
		{
			if (!array_key_exists($key, $array))
			{
				$this->addError($model, $attribute, "Image must contain a `$key` key.");
			}
			else if ($required && empty($array[$key]))
			{
				$this->addError($model, $attribute, "Image key `$key` is required.");
			}
			else
			{
				$valid = true;
			}
		}

		return $valid;
	}
}