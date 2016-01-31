<?php

namespace Opengraph\Tests\Units;

require_once __DIR__ . '/../../src/Opengraph/Test/Unit.php';
require_once __DIR__ . '/../../src/Opengraph/Meta.php';
require_once __DIR__ . '/../../src/Opengraph/Opengraph.php';

use Opengraph;

class Meta extends Opengraph\Test\Unit
{    
    public function testMeta()
    {
        $meta = new Opengraph\Meta(Opengraph\Opengraph::OG_TITLE, 'test');
        
        $this->assert->object($meta)
            ->isInstanceOf('\Opengraph\Meta');
        
        $this->assert->string($meta->getProperty())
            ->isEqualTo('og:title');
            
        $this->assert->string($meta->getContent())
            ->isEqualTo('test');
        
        $this->assert->string($meta->render())
            ->isEqualTo('<meta property="og:title" content="test" />');
        
        $meta->setProperty(Opengraph\Opengraph::OG_TYPE);
        
        $this->assert->string($meta->getProperty())
            ->isEqualTo('og:type');
        
        $meta->setContent(Opengraph\Opengraph::TYPE_BOOK);
        
        $this->assert->string($meta->getContent())
            ->isEqualTo('book');
        
        $this->assert->string($meta->render())
            ->isEqualTo('<meta property="og:type" content="book" />');
        
        $meta->setContent(array(123, 456));
        
        $this->assert->string($meta->render())
            ->isEqualTo('<meta property="og:type" content="123,456" />');
    }
}