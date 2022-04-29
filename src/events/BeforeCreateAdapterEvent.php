<?php

namespace spicyweb\embeddedassets\events;

use yii\base\Event;

/**
 * Event for modifying the Embed adapter options before adapter creation.
 *
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.8.0
 */
class BeforeCreateAdapterEvent extends Event
{
    /**
     * @var string
     */
    public string $url;

    /**
     * @var array
     * @see https://github.com/oscarotero/Embed/tree/3.4.17#the-adapter
     */
    public array $options = [];

    /**
     * @var array
     * @since 2.8.1
     * @see https://github.com/oscarotero/Embed/tree/3.4.17#the-dispatcher
     */
    public array $dispatcherConfig = [];
}
