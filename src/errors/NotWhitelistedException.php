<?php

namespace spicyweb\embeddedassets\errors;

use yii\base\Exception;

/**
 * NotWhitelistedException represents an exception when the `preventNonWhitelistedUploads` setting is enabled and an
 * uploaded embedded asset's provider isn't whitelisted.
 *
 * @package spicyweb\embeddedassets\errors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 3.1.0
 */
class NotWhitelistedException extends Exception
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Tried to upload embedded asset with non-whitelisted provider';
    }
}
