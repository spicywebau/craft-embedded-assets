# Opengraph

[![Build Status](https://secure.travis-ci.org/euskadi31/Opengraph.png)](http://travis-ci.org/euskadi31/Opengraph)

## Test with [Atoum](https://github.com/atoum/atoum)

	cd Opengraph/
    curl -s https://getcomposer.org/installer | php
    php composer.phar install --dev
	./vendor/atoum/atoum/bin/atoum --glob Tests/Units/


## Writer

``` php
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

?>
```

Output

``` html
    <meta property="og:title" content="The Rock" />
    <meta property="og:type" content="video.movie" />
    <meta property="og:url" content="http://www.imdb.com/title/tt0117500/" />
    <meta property="og:image" content="http://ia.media-imdb.com/images/rock.jpg" />
```

## Reader

``` php
<?php
namespace Application;

require_once __DIR__ . '/../src/Opengraph/Meta.php';
require_once __DIR__ . '/../src/Opengraph/Opengraph.php';
require_once __DIR__ . '/../src/Opengraph/Reader.php';

use Opengraph;

$reader = new Opengraph\Reader();
$reader->parse(file_get_contents('http://www.imdb.com/title/tt0117500/'));
print_r($reader->getArrayCopy());

?>
```

Output

    Array
    (
        [og:url] => http://www.imdb.com/title/tt0117500/
        [og:title] => Rock (1996)
        [og:type] => video.movie
        [og:image] => Array
            (
                [0] => Array
                    (
                        [og:image:url] => http://ia.media-imdb.com/images/M/MV5BMTM3MTczOTM1OF5BMl5BanBnXkFtZTYwMjc1NDA5._V1._SX98_SY140_.jpg
                    )

            )

        [og:site_name] => IMDb
        [fb:app_id] => 115109575169727
    )
