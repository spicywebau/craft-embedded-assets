<?php

namespace spicyweb\embeddedassets\validators;

use Twig\Markup;
use yii\validators\Validator;

/**
 * Class TwigMarkup
 *
 * Validates an instance of \Twig\Markup
 *
 * @package spicyweb\embeddedassets\validators
 * @author Spicy Web <craft@spicyweb.com.au>
 * @author Benjamin Fleming
 * @since 1.0.0
 */
class TwigMarkup extends Validator
{
    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute): void
    {
        $markup = $model->$attribute;

        if (!($markup instanceof Markup)) {
            $this->addError($model, $attribute, "Code must be Twig markup.");
        }
    }
}
