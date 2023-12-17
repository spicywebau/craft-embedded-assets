<?php

namespace spicyweb\embeddedassets\events;

use Embed\Embed;
use yii\base\Event;

/**
 * Event for modifying the Embed options before making a request for an embedded asset.
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
     * @var Embed
     */
    public Embed $embed;
}
