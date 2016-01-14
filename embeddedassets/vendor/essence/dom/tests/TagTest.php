<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license MIT
 */
namespace Essence\Dom;

use PHPUnit_Framework_TestCase as TestCase;



/**
 *	Test case for Tag.
 */
class TagTest extends TestCase {

	/**
	 *
	 */
	public function testMatches() {
		$Tag = $this->getMockForAbstractClass('Essence\Dom\Tag');
		$Tag->expects($this->once())
			->method('get')
			->with($this->equalTo('foo'))
			->will($this->returnValue('bar'));

		$result = $Tag->matches('foo', '~b(a)r~', $matches);

		$this->assertEquals(1, $result);
		$this->assertEquals(['bar', 'a'], $matches);
	}
}
