<?php

namespace Application;

require_once __DIR__ . '/../src/Opengraph/Meta.php';
require_once __DIR__ . '/../src/Opengraph/Opengraph.php';
require_once __DIR__ . '/../src/Opengraph/Reader.php';

use Opengraph;

$reader = new Opengraph\Reader();
$reader->parse(file_get_contents('http://www.imdb.com/title/tt0117500/'));
print_r($reader->getArrayCopy());