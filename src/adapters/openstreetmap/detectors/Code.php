<?php

namespace spicyweb\embeddedassets\adapters\openstreetmap\detectors;

use Craft;
use craft\helpers\Html;
use craft\helpers\UrlHelper as Url;
use Embed\Detectors\Code as BaseCodeDetector;
use Embed\EmbedCode;

/**
 * Embed code detector class for OpenStreetMap.
 *
 * @package spicyweb\embeddedassets\adapters\openstreetmap\detectors
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 4.1.0
 */
class Code extends BaseCodeDetector
{
    /**
     * @var string Regular expression for matching a request fragment for an embeddable map
     */
    public static string $requestFragmentMapPattern = '/^map=([0-9]{1,2})\/(-?[0-9]{1,3})(\.[0-9]+)?\/(-?[0-9]{1,3})(\.[0-9]+)?(&layers=[CHTY]|$)/';

    /**
     * @var string Base URL for OpenStreetMap embeds
     */
    private static $_embedBaseUrl = 'https://www.openstreetmap.org/export/embed.html';

    public function detect(): ?EmbedCode
    {
        $requestFragment = $this->extractor->getRequest()->getUri()->getFragment();

        if (!$requestFragment || !preg_match(self::$requestFragmentMapPattern, $requestFragment, $matches)) {
            return parent::detect();
        }

        $request = Craft::$app->getRequest();
        $zoom = (int)$matches[1];
        $lat = (float)sprintf('%s%s', $matches[2], $matches[3] ?: '');
        $lng = (float)sprintf('%s%s', $matches[4], $matches[5] ?: '');
        $markerLat = $request->getParam('mlat', $lat);
        $markerLng = $request->getParam('mlon', $lng);
        $layerMatch = !empty($matches[6])
            ? explode('=', $matches[6])[1]
            : null;
        $layer = match ($layerMatch) {
            'C' => 'cyclemap',
            'H' => 'hot',
            'T' => 'transportmap',
            'Y' => 'cyclosm',
            default => 'mapnik',
        };

        // Embed code on the OpenStreetMap website is generated based on the visible part of the map, which we can't do
        // Calculate the map bounds based on level of accuracy for closer zoom levels in embedded asset previews
        $difference = 2.3 ** (19 - $zoom) / 10000;
        $north = $lat - $difference;
        $south = $lat + $difference;
        $west = $lng - $difference;
        $east = $lng + $difference;
        $iframe = Html::tag('iframe', '', [
            'src' => Url::urlWithParams(self::$_embedBaseUrl, [
                'bbox' => "$west,$south,$east,$north",
                'layer' => $layer,
                'marker' => "$markerLat,$markerLng",
            ]),
        ]);

        return new EmbedCode(htmlspecialchars_decode($iframe, ENT_QUOTES | ENT_HTML5));
    }
}
