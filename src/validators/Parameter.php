<?php

namespace spicyweb\embeddedassets\validators;

use yii\validators\Validator;

/**
 * Class Parameter
 *
 * Validates an array matching the following signature (*required):
 * [
 *     'param' -> string *
 *     'value' -> string *
 * ]
 *
 * @package spicyweb\embeddedassets\validators
 * @author Spicy Web <craft@spicyweb.com.au>
 * @author Benjamin Fleming
 * @since 1.0.0
 */
class Parameter extends Validator
{
    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute): void
    {
        $parameter = $model->$attribute;
        
        if (!is_array($parameter)) {
            $this->addError($model, $attribute, "Parameter must be an array.");
        } else {
            foreach (['param', 'value'] as $key) {
                if ($this->validateKeyExists($model, $attribute, $key, true) && !is_string($parameter[$key])) {
                    $this->addError($model, $attribute, "Parameter key `$key` must be a string.");
                }
            }
        }
    }
    
    /**
     * @param $model
     * @param $attribute
     * @param string $key
     * @param bool $required
     * @return bool
     */
    protected function validateKeyExists($model, $attribute, string $key, bool $required = false): bool
    {
        $array = $model->$attribute;
        $valid = false;
        
        if (is_array($array)) {
            if (!array_key_exists($key, $array)) {
                $this->addError($model, $attribute, "Parameter must contain a `$key` key.");
            } else {
                if ($required && strlen($array[$key]) === 0) {
                    $this->addError($model, $attribute, "Parameter key `$key` is required.");
                } else {
                    $valid = true;
                }
            }
        }
        
        return $valid;
    }
}
