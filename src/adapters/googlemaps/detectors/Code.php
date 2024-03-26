<?php

/*
This class is based on the `Embed\Providers\Api\GoogleMaps` class from Embed 3.4.18.
https://github.com/oscarotero/Embed/blob/v3.4.18/src/Providers/Api/GoogleMaps.php
Embed is released under the terms of the MIT License, a copy of which is included below.
https://github.com/oscarotero/Embed/blob/v3.4.18/LICENSE

The MIT License (MIT)

Copyright (c) 2017 Oscar Otero Marzoa

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace spicyweb\embeddedassets\adapters\googlemaps\detectors;

use craft\helpers\Html;
use craft\helpers\UrlHelper as Url;
use Embed\Detectors\Code as BaseCodeDetector;
use Embed\EmbedCode;
use function Embed\getDirectory;

/**
 * Embed code detector class for Google Maps.
 *
 * @package spicyweb\embeddedassets\adapters\googlemaps\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.0.0
 */
class Code extends BaseCodeDetector
{
    public function detect(): ?EmbedCode
    {
        $mode = $this->extractor->getMode();
        $requestUrl = $this->extractor->getRequest()->getUri();
        $responsePath = $requestUrl->getPath();
        $pos = $this->_getPosition($mode, $responsePath);
        $iframeUrl = Url::hostInfo($requestUrl) . '/maps/embed/v1/' . match ($mode) {
            'dir' => 'directions',
            default => $mode,
        };
        $params = match ($mode) {
            'view' => [
                'center' => $pos['coordinates'],
                'zoom' => $pos['zoom'],
            ],
            'streetview' => [
                'location' => $pos['coordinates'],
                'heading' => $pos['heading'],
                'pitch' =>  $pos['pitch'],
                'fov' =>  $pos['fov'],
            ],
            'place' => [
                'q' => getDirectory($responsePath, 2),
            ],
            'dir' => [
                'origin' => getDirectory($responsePath, 2),
                'destination' => getDirectory($responsePath, 3),
            ],
        };

        $iframe = Html::tag('iframe', '', [
            'src' => Url::urlWithParams($iframeUrl, $params + [
                'key' => $this->extractor->getSetting('google:key'),
            ]),
        ]);
        
        return new EmbedCode(htmlspecialchars_decode($iframe, ENT_QUOTES | ENT_HTML5));
    }

    /**
     * Returns parsed position data from path.
     *
     * @param string $mode The URL mode
     * @param string $path
     * @return array
     */
    private function _getPosition(string $mode, string $path): array
    {
        // Set defaults
        $position = [
            'coordinates' => '',
            'zoom' => '4',
            'heading' => '0',
            'pitch' => '0',
            'fov' => '90'
        ];

        if ($mode === 'view') {
            $pos = explode(',', getDirectory($path, 1));
            $position['coordinates'] = str_replace('@', '', $pos[0]).','.$pos[1];
            $position['zoom'] = str_replace('z', '', $pos[2]);
        }

        if ($mode === 'streetview') {
            $pos = explode(',', getDirectory($path, 1));
            $position['coordinates'] = str_replace('@', '', $pos[0]).','.$pos[1];
            $position['zoom'] = str_replace('a', '', $pos[2]); // seems not used by google (emulated by other params)
            $position['heading'] = str_replace('h', '', $pos[4]);
            $position['fov'] = str_replace('y', '', $pos[3]);
            $pitch = str_replace('t', '', $pos[5]); // t is pitch but in 180% format
            if (is_numeric($pitch)) {
                $position['pitch'] = floatval($pitch) - 90;
            }
        }

        return $position;
    }
}
