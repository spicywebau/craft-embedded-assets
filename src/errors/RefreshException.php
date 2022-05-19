<?php

namespace spicyweb\embeddedassets\errors;

use yii\base\Exception;

/**
 * RefreshException represents an exception when refreshing an embedded asset.
 *
 * @package spicyweb\embeddedassets\errors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 3.0.2
 */
class RefreshException extends Exception
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Failed to refresh embedded asset';
    }
}
