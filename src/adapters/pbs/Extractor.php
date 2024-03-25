<?php

namespace spicyweb\embeddedassets\adapters\pbs;

use spicyweb\embeddedassets\adapters\default\Extractor as BaseExtractor;
use spicyweb\embeddedassets\adapters\pbs\detectors\Code;
use spicyweb\embeddedassets\adapters\pbs\detectors\Type;

/**
 * Embed extractor class for PBS websites.
 *
 * @package spicyweb\embeddedassets\adapters\pbs
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Extractor extends BaseExtractor
{
    /**
     * @var string|false|null iframe string, false if response has no iframe, null if response not yet checked
     */
    private string|false|null $_iframe = null;

    public function createCustomDetectors(): array
    {
        return [
            'code' => new Code($this),
            'type' => new Type($this),
        ] + parent::createCustomDetectors();
    }

    /**
     * Returns the iframe code from the response, if the response contains an iframe.
     *
     * @return string|null
     */
    public function getIframe(): ?string
    {
        if ($this->_iframe === null) {
            $extractorHtml = (string)$this->getResponse()->getBody();
            $matches = [];

            if (
                preg_match('/&lt;iframe(.+)iframe&gt;/i', $extractorHtml, $matches) &&
                preg_match('/https:\\/\\/player.pbs.org\\/viralplayer\\/([0-9]+)\\//i', $matches[0])
            ) {
                $this->_iframe = $matches[0];
            } else {
                $this->_iframe = false;
            }
        }

        return $this->_iframe ?: null;
    }
}
