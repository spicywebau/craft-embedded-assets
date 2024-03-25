<?php

namespace spicyweb\embeddedassets\adapters\pbs\detectors;

use Embed\Detectors\Code as BaseCodeDetector;
use Embed\EmbedCode;

/**
 * Embed code detector class for PBS websites.
 *
 * @package spicyweb\embeddedassets\adapters\pbs\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Code extends BaseCodeDetector
{
    public function detect(): ?EmbedCode
    {
        $iframe = $this->extractor->getIframe();

        return $iframe !== null
            ? new EmbedCode(htmlspecialchars_decode($iframe, ENT_QUOTES | ENT_HTML5))
            : parent::detect();
    }
}
