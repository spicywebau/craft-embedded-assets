<?php

namespace spicyweb\embeddedassets\errors;

use spicyweb\embeddedassets\models\EmbeddedAsset;
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
     * @var EmbeddedAsset
     */
    public EmbeddedAsset $embeddedAsset;

    /**
     * @param string $message
     * @param EmbeddedAsset $embeddedAsset
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', EmbeddedAsset $embeddedAsset, int $code = 0, ?Throwable $previous = null)
    {
        $this->embeddedAsset = $embeddedAsset;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Tried to upload embedded asset with non-whitelisted provider';
    }
}
