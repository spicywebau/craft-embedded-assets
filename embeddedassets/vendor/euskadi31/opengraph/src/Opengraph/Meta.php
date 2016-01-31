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

class Meta
{
    /**
     * @var String
     */
    protected $_property;
    
    /**
     * @var Mixed
     */
    protected $_content;
    
    /**
     * @param String $property
     * @param String $content
     * @return \Opengraph\Meta
     */
    public function __construct($property, $content)
    {
        $this->_property = $property;
        $this->_content = $content;
    }
    
    /**
     * Set property
     * 
     * @param String $property
     * @return \Opengraph\Meta
     */
    public function setProperty($property)
    {
        $this->_property = $property;
        
        return $this;
    }
    
    /**
     * Get property name
     * 
     * @return String
     */
    public function getProperty()
    {
        return $this->_property;
    }
    
    /**
     * Set content
     * 
     * @param String $content
     * @return \Opengraph\Meta
     */
    public function setContent($content)
    {
        $this->_content = $content;
        
        return $this;
    }
    
    /**
     * Get content
     * 
     * @return String
     */
    public function getContent()
    {
        if(is_array($this->_content)) {
            return implode(',', $this->_content);
        }
        return $this->_content;
    }
    
    /**
     * Render meta tag
     * 
     * @return String
     */
    public function render()
    {
        return '<meta property="' . $this->getProperty() . '" content="' . $this->getContent() . '" />';
    }
}