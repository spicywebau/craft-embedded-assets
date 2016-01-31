<?php

namespace Application;

require_once __DIR__ . '/../src/Opengraph/Meta.php';
require_once __DIR__ . '/../src/Opengraph/Opengraph.php';
require_once __DIR__ . '/../src/Opengraph/Writer.php';

use Opengraph;

$writer = new Opengraph\Writer();
$writer->append(Opengraph\Writer::OG_TITLE, 'The Rock');
$writer->append(Opengraph\Writer::OG_TYPE, Opengraph\Writer::TYPE_VIDEO_MOVIE);
$writer->append(Opengraph\Writer::OG_URL, 'http://www.imdb.com/title/tt0117500/');
$writer->append(Opengraph\Writer::OG_IMAGE, 'http://ia.media-imdb.com/images/rock.jpg');

echo $writer->render() . PHP_EOL;