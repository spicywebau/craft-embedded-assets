<?php
/**
 * @package     Opengraph
 * @author      Axel Etcheverry <axel@etcheverry.biz>
 * @copyright   Copyright (c) 2011 Axel Etcheverry (http://www.axel-etcheverry.com)
 * Displays     <a href="http://creativecommons.org/licenses/MIT/deed.fr">MIT</a>
 * @license     http://creativecommons.org/licenses/MIT/deed.fr    MIT
 */

/**
 * @namespace
 */
namespace Opengraph;

use ArrayObject;
use Iterator;
use ArrayIterator;
use Serializable;
use Countable;

abstract class Opengraph implements Iterator, Serializable, Countable
{
    /**
     * Basic Metadata
     */
    const OG_TITLE              = 'og:title';
    const OG_TYPE               = 'og:type';
    const OG_IMAGE              = 'og:image';
    const OG_URL                = 'og:url';

    /**
     * Optional Metadata
     */
    const OG_IMAGE_SECURE_URL   = 'og:image:secure_url';
    const OG_IMAGE_TYPE         = 'og:image:type';
    const OG_IMAGE_WIDTH        = 'og:image:width';
    const OG_IMAGE_HEIGHT       = 'og:image:height';

    const OG_AUDIO              = 'og:audio';
    const OG_AUDIO_SECURE_URL   = 'og:audio:secure_url';
    const OG_AUDIO_TYPE         = 'og:audio:type';

    const OG_DESCRIPTION        = 'og:description';
    const OG_DETERMINER         = 'og:determiner';
    const OG_LOCALE             = 'og:locale';
    const OG_LOCALE_ALTERNATE   = 'og:locale:alternate';
    const OG_SITE_NAME          = 'og:site_name';

    const OG_VIDEO              = 'og:video';
    const OG_VIDEO_SECURE_URL   = 'og:video:secure_url';
    const OG_VIDEO_TYPE         = 'og:video:type';
    const OG_VIDEO_WIDTH        = 'og:video:width';
    const OG_VIDEO_HEIGHT       = 'og:video:height';

    /**
     * Facebook Metadata
     */
    const FB_ADMINS             = 'fb:admins';
    const FB_PAGE               = 'fb:page_id';
    const FB_APP                = 'fb:app_id';

    /**
     * DEPRECATED PROPERTIES!  DO NOT READ ON UNLESS YOU MUST!
     */
    const OG_LATITUDE           = 'og:latitude';
    const OG_LONGITUDE          = 'og:longitude';
    const OG_STREET_ADDRESS     = 'og:street-address';
    const OG_LOCALITY           = 'og:locality';
    const OG_REGION             = 'og:region';
    const OG_POSTAL_CODE        = 'og:postal-code';
    const OG_COUNTRY_NAME       = 'og:country-name';
    const OG_EMAIL              = 'og:email';
    const OG_PHONE_NUMBER       = 'og:phone_number';
    const OG_FAX_NUMBER         = 'og:fax_number';
    const OG_ISBN               = 'og:isbn';
    const OG_UPC                = 'og:upc';
    const OG_AUDIO_TITLE        = 'og:audio:title';
    const OG_AUDIO_ARTIST       = 'og:audio:artist';
    const OG_AUDIO_ALBUM        = 'og:audio:album';

    /**
     * Types
     */
    const TYPE_MUSIC_SONG           = 'music.song';
    const TYPE_MUSIC_ALBUM          = 'music.album';
    const TYPE_MUSIC_PLAYLIST       = 'music.playlist';
    const TYPE_MUSIC_RADIOSTATION   = 'music.radio_station';

    const TYPE_VIDEO_MOVIE          = 'video.movie';
    const TYPE_VIDEO_EPISODE        = 'video.episode';
    const TYPE_VIDEO_TVSHOW         = 'video.tv_show';
    const TYPE_VIDEO_OTHER          = 'video.other';

    const TYPE_ARTICLE              = 'article';
    const TYPE_BOOK                 = 'book';
    const TYPE_PROFILE              = 'profile';
    const TYPE_WEBSITE              = 'website';

    /**
     * Article content fields
     */
    const ARTICLE_PUBLISHED_TIME    = 'article:published_time';
    const ARTICLE_MODIFIED_TIME     = 'article:modified_time';
    const ARTICLE_AUTHOR            = 'article:author';
    const ARTICLE_SECTION           = 'article:section';
    const ARTICLE_TAG               = 'article:tag';

    /**
     * Positions
     */
    const APPEND                = 'append';
    const PREPEND               = 'prepend';

    /**
     * @var \ArrayObject
     */
    protected static $storage;

    /**
     * @var Integer
     */
    protected $_position = 0;


    public function __construct()
    {
        if(is_null(static::$storage)) {
            static::$storage = new ArrayObject();
            //static::$position = 0;
        }
    }

    /**
     * Add meta
     *
     * @param String $property
     * @param Mixed $content
     * @param String $position
     * @return \Opengraph\Opengraph
     */
    public function addMeta($property, $content, $position)
    {
        $content = $this->_normalizeContent($property, $content);

        switch($property) {
            case self::OG_TITLE:
            case self::OG_TYPE:
            case self::OG_DESCRIPTION:
            case self::OG_LOCALE:
            case self::OG_SITE_NAME:
            case self::OG_URL:
                if($this->hasMeta($property)) {
                    $this->removeMeta($property);
                    //$this->getMeta($property)->setContent($content);
                }
                break;
        }

        if($position == self::APPEND) {
            static::$storage->append(new Meta($property, $content));
        } else {
            $values = static::$storage->getArrayCopy();
            array_unshift($values, new Meta($property, $content));
            static::$storage->exchangeArray($values);
            unset($values);
        }

        return $this;
    }

    /**
     * Check is meta exists
     *
     * @param String $property
     * @return Boolean
     */
    public function hasMeta($property)
    {
        foreach(static::$storage as $meta) {
            if($meta->getProperty() == $property) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get meta by property name
     *
     * @param String $property
     * @return \Opengraph\Meta
     */
    public function getMeta($property)
    {
        foreach(static::$storage as $meta) {
            if($meta->getProperty() == $property) {
                return $meta->getContent();
            }
        }

        return false;
    }

    /**
     * Remove meta
     *
     * @param String $property
     * @return Boolean
     */
    public function removeMeta($property)
    {
        foreach(static::$storage as $i => $meta) {
            if($meta->getProperty() == $property) {
                unset(static::$storage[$i]);
                return true;
            }
        }

        return false;
    }

    /**
     * @return \ArrayObject
     */
    public function getMetas()
    {
        return static::$storage;
    }

    /**
     * Normalize content
     *
     * @param String $property
     * @param Mixed $content
     * @return Mixed
     */
    protected function _normalizeContent($property, $content)
    {
        if($property == self::FB_ADMINS && is_string($content)) {
            return (array)explode(',', $content);
        }

        return $content;
    }

    /**
     * Get array
     *
     * @return Array
     */
    public function getArrayCopy()
    {
        $graph = array();

        $metas = static::$storage->getArrayCopy();

        foreach($metas as $i => $meta) {

            $property   = $meta->getProperty();
            $content    = $meta->getContent();

            switch($property) {

                case self::OG_IMAGE:

                    $data = array(
                        $property . ':url' => $content
                    );

                    for($j = ($i+1); $j <= ($i+4); $j++) {

                        if(isset($metas[$j])) {
                            $next = $metas[$j];

                            if(!empty($next)) {
                                $nextProperty = $next->getProperty();

                                switch($nextProperty) {
                                    case self::OG_IMAGE_SECURE_URL:
                                    case self::OG_IMAGE_HEIGHT:
                                    case self::OG_IMAGE_WIDTH:
                                    case self::OG_IMAGE_TYPE:
                                        if(!isset($data[$nextProperty])) {
                                            $data[$nextProperty] = $next->getContent();
                                            unset($metas[$j]);
                                        }
                                        break;
                                }
                            }
                        }
                    }

                    if(!isset($graph[$property])) {
                        $graph[$property] = array();
                    }

                    $graph[$property][] = $data;
                    unset($data);

                    break;

                case self::OG_VIDEO:
                    $data = array(
                        $property . ':url' => $content
                    );

                    for($j = ($i+1); $j <= ($i+4); $j++) {
                        if(isset($metas[$j])) {
                            $next = $metas[$j];
                            if(!empty($next)) {
                                $nextProperty = $next->getProperty();

                                switch($nextProperty) {
                                    case self::OG_VIDEO_SECURE_URL:
                                    case self::OG_VIDEO_HEIGHT:
                                    case self::OG_VIDEO_WIDTH:
                                    case self::OG_VIDEO_TYPE:
                                        if(!isset($data[$nextProperty])) {
                                            $data[$nextProperty] = $next->getContent();
                                            unset($metas[$j]);
                                        }
                                        break;
                                }
                            }
                        }
                    }

                    if(!isset($graph[$property])) {
                        $graph[$property] = array();
                    }

                    $graph[$property][] = $data;
                    unset($data);

                    break;

                case self::OG_AUDIO:
                    $data = array(
                        $property . ':url' => $content
                    );

                    for($j = ($i+1); $j <= ($i+2); $j++) {
                        if(isset($metas[$j])) {
                            $next = $metas[$j];
                            if(!empty($next)) {
                                $nextProperty = $next->getProperty();

                                switch($nextProperty) {
                                    case self::OG_AUDIO_SECURE_URL:
                                    case self::OG_AUDIO_TYPE:
                                        if(!isset($data[$nextProperty])) {
                                            $data[$nextProperty] = $next->getContent();
                                            unset($metas[$j]);
                                        }
                                        break;
                                }
                            }
                        }
                    }

                    if(!isset($graph[$property])) {
                        $graph[$property] = array();
                    }

                    $graph[$property][] = $data;
                    unset($data);

                    break;

                default:
                    $denyProperties = array(
                        self::OG_AUDIO_SECURE_URL,
                        self::OG_AUDIO_TYPE,
                        self::OG_VIDEO_SECURE_URL,
                        self::OG_VIDEO_HEIGHT,
                        self::OG_VIDEO_WIDTH,
                        self::OG_VIDEO_TYPE,
                        self::OG_IMAGE_SECURE_URL,
                        self::OG_IMAGE_HEIGHT,
                        self::OG_IMAGE_WIDTH,
                        self::OG_IMAGE_TYPE
                    );

                    if(!in_array($property, $denyProperties)) {
                        $graph[$property] = $content;
                        unset($metas[$i]);
                    }

            }
        }
        unset($metas);

        return $graph;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind()
    {
        reset(static::$storage);
        $this->_position = 0;
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        return current(static::$storage);
    }

    /**
     * Return the key of the current element
     *
     * @return scalar
     */
	public function key()
	{
	    return key(static::$storage);
	}

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        next(static::$storage);
        ++$this->_position;
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->_position < sizeof(static::$storage);
    }

    /**
     * Count elements of an object
     *
     * @return integer
     */
    public function count()
    {
        return count(static::$storage);
    }

    /**
     * String representation of object
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(static::$storage);
    }

    /**
     * Constructs the object form string
     *
     * @param  string $data The string representation of the object.
     * @return void
     */
    public function unserialize($data)
    {
        static::$storage = unserialize($data);
    }
}