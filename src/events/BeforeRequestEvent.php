<?php

namespace spicyweb\embeddedassets\events;

use yii\base\Event;

/**
 * Event for modifying the Embed adapters and settings before making a request for an embedded asset.
 *
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class BeforeRequestEvent extends Event
{
    /**
     * @var string
     */
    public string $url;

    /**
     * @var array
     * @see https://github.com/oscarotero/Embed/tree/v4.4.10#adapters
     */
    public array $adapters;

    /**
     * @var array
     * @see https://github.com/oscarotero/Embed/tree/v4.4.10#settings
     */
    public array $clientSettings;

    /**
     * @var array
     * @see https://github.com/oscarotero/Embed/tree/v4.4.10#settings
     */
    public array $embedSettings;
}
