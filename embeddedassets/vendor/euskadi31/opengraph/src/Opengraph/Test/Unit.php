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
namespace Opengraph\Test;

use \mageekguy\atoum;
use \mageekguy\atoum\factory;

abstract class Unit extends atoum\test 
{
    public function __construct(factory $factory = null)
    {
        $this->setTestNamespace('Tests\\Units');
        parent::__construct($factory);
    }
}