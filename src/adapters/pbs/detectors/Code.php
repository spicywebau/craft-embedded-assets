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
        $extractorHtml = (string)$this->extractor->getResponse()->getBody();
        $matches = [];

        if (preg_match('/&lt;iframe(.+)iframe&gt;/i', $extractorHtml, $matches)) {
            if (preg_match('/https:\\/\\/player.pbs.org\\/viralplayer\\/([0-9]+)\\//i', $matches[0])) {
                return new EmbedCode(htmlspecialchars_decode($matches[0], ENT_QUOTES | ENT_HTML5));
            }
        }

        return parent::detect();
    }
}
