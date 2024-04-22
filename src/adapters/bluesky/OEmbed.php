<?php

namespace spicyweb\embeddedassets\adapters\bluesky;

use Embed\OEmbed as BaseOEmbed;
use Psr\Http\Message\UriInterface;

/**
 * OEmbed class for Bluesky.
 *
 * @package spicyweb\embeddedassets\adapters\bluesky
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.1.0
 */
class OEmbed extends BaseOEmbed
{
    protected function detectEndpoint(): ?UriInterface
    {
        $uri = $this->extractor->getUri();
        $queryParameters = $this->getOembedQueryParameters((string)$uri);

        // Bluesky requires 220 <= `maxwidth` <= 550
        if (isset($queryParameters['maxwidth'])) {
            $queryParameters['maxwidth'] = min(
                550,
                max(
                    220,
                    $queryParameters['maxwidth'],
                ),
            );
        }

        return $this->extractor->getCrawler()
            ->createUri('https://embed.bsky.app/oembed')
            ->withQuery(http_build_query($queryParameters));
    }
}
