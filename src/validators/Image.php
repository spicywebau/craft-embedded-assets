<?php

namespace spicyweb\embeddedassets\validators;

use craft\helpers\UrlHelper;

use yii\validators\Validator;

/**
 * Class Image
 *
 * Validates an array matching the following signature (*required):
 * [
 *     'url' -> string *
 *     'width' -> number *
 *     'height' -> number *
 *     'size' -> number *
 *     'mime' -> string
 * ]
 *
 * @package spicyweb\embeddedassets\validators
 * @author Spicy Web <craft@spicyweb.com.au>
 * @author Benjamin Fleming
 * @since 1.0.0
 */
class Image extends Validator
{
    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute): void
    {
        $image = $model->$attribute;
        
        if (!is_array($image)) {
            $this->addError($model, $attribute, "Image must be an array.");
        } else {
            if ($this->validateKeyExists($model, $attribute, 'url', true)
                && !UrlHelper::isAbsoluteUrl($image['url'])
                && strpos('data:image', $image['url']) === -1
            ) {
                $this->addError($model, $attribute, "Image key `url` must be an absolute URL.");
            }
            
            foreach (['width', 'height', 'size'] as $key) {
                if ($this->validateKeyExists($model, $attribute, $key, true) && !is_numeric($image[$key])) {
                    $this->addError($model, $attribute, "Image key `$key` must be a number.");
                }
            }
            
            if (
                $this->validateKeyExists($model, $attribute,
                    'mime') && !empty($image['mime']) && !is_string($image['mime'])
            ) {
                $this->addError($model, $attribute, "Image key `mime` must be a string.");
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
                $this->addError($model, $attribute, "Image must contain a `$key` key.");
            } else {
                if ($required && empty($array[$key])) {
                    $this->addError($model, $attribute, "Image key `$key` is required.");
                } else {
                    $valid = true;
                }
            }
        }
        
        return $valid;
    }
}
