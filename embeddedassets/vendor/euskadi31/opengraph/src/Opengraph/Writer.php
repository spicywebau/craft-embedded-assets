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

class Writer extends Opengraph
{
    /**
     * @var \ArrayObject
     */
    protected static $storage;
    
    /**
     * @var Integer
     */
    //protected static $position;
    
    /**
     * Append meta
     * 
     * @param String $property
     * @param String $content
     * @return \Opengraph\Opengraph
     */
    public function append($property, $content)
    {
        return $this->addMeta($property, $content, self::APPEND);
    }

    /**
     * Prepend meta
     * 
     * @param String $property
     * @param String $content
     * @return \Opengraph\Opengraph
     */
    public function prepend($property, $content)
    {
        return $this->addMeta($property, $content, self::PREPEND);
    }
    
    /**
     * Render all meta tags
     * 
     * @return String
     */
    public function render()
    {
        $html = '';
        foreach(self::$storage as $meta) {
            $html .= "\t" . $meta->render() . PHP_EOL;
        }
        
        return $html;
    }
}