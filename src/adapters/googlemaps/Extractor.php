<?php

namespace spicyweb\embeddedassets\adapters\googlemaps;

use Embed\Http\Crawler;
use function Embed\getDirectory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use spicyweb\embeddedassets\adapters\default\Extractor as BaseExtractor;
use spicyweb\embeddedassets\adapters\googlemaps\detectors\Code;
use spicyweb\embeddedassets\adapters\googlemaps\detectors\ProviderName;
use spicyweb\embeddedassets\adapters\googlemaps\detectors\Title;
use spicyweb\embeddedassets\adapters\googlemaps\detectors\Type;

/**
 * Embed extractor class for Google Maps.
 *
 * @package spicyweb\embeddedassets\adapters\googlemaps
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Extractor extends BaseExtractor
{
    private string $_mode;
    public function __construct(
        UriInterface $uri,
        RequestInterface $request,
        ResponseInterface $response,
        Crawler $crawler
    ) {
        parent::__construct($uri, $request, $response, $crawler);
        $requestPath = $this->getRequest()->getUri()->getPath();
        $mode = getDirectory($requestPath, 1);

        if ((substr($mode, 0, 1) === '@') &&  (substr($mode, -1) === 't')) {
            $this->_mode = 'streetview';
        } else {
            $this->_mode = match ($mode) {
                'place', 'dir', 'search' => $mode,
                default => 'view',
            };
        }
    }

    public function createCustomDetectors(): array
    {
        return [
            'code' => new Code($this),
            'providerName' => new ProviderName($this),
            'title' => new Title($this),
            'type' => new Type($this),
        ] + parent::createCustomDetectors();
    }

    public function getMode(): string
    {
        return $this->_mode;
    }
}
