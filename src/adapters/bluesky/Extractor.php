<?php

namespace spicyweb\embeddedassets\adapters\bluesky;

use Embed\Http\Crawler;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use spicyweb\embeddedassets\adapters\default\Extractor as BaseExtractor;
use spicyweb\embeddedassets\adapters\bluesky\detectors\Code;

/**
 * Embed extractor class for Bluesky.
 *
 * @package spicyweb\embeddedassets\adapters\bluesky
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.1.0
 */
class Extractor extends BaseExtractor
{
    public function __construct(UriInterface $uri, RequestInterface $request, ResponseInterface $response, Crawler $crawler)
    {
        parent::__construct($uri, $request, $response, $crawler);
        $this->oembed = new OEmbed($this);
    }
}
