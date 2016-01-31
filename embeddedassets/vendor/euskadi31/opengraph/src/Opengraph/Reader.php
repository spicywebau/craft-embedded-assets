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

use DOMDocument;
use RuntimeException;
use ArrayObject;

class Reader extends Opengraph
{
    /**
     * @var \ArrayObject
     */
    protected static $storage;
    
    public function __construct()
    {
        static::$storage = new ArrayObject();
    }

    /**
     * Parse html tags
     * 
     * @param String $contents
     * @return Array
     */
    public function parse($contents)
    {
        $old_libxml_error = libxml_use_internal_errors(true);
        
        $dom = new DOMDocument;
        
        if(@$dom->loadHTML($contents) === false) {
            throw new RuntimeException("Contents is empty");
        }
        
        libxml_use_internal_errors($old_libxml_error);
        
        foreach($dom->getElementsByTagName('meta') as $tag) { 
            if ($tag->hasAttribute('property') && $tag->hasAttribute('content')) {
                $this->addMeta($tag->getAttribute('property'), $tag->getAttribute('content'), self::APPEND);
            } 
        }
        unset($dom);
        
        return $this;
    }
}