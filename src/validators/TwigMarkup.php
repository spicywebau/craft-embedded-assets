<?php
namespace benf\embeddedassets\validators;

use yii\validators\Validator;

/**
 * Class TwigMarkup
 * @package benf\embeddedassets\validators
 *
 * Validates an instance of \Twig_Markup
 */
class TwigMarkup extends Validator
{
	/**
	 * @param \yii\base\Model $model
	 * @param string $attribute
	 */
	public function validateAttribute($model, $attribute)
	{
		$markup = $model->$attribute;

		if (!($markup instanceof \Twig_Markup))
		{
			$this->addError($model, $attribute, "Code must be Twig markup.");
		}
	}
}
