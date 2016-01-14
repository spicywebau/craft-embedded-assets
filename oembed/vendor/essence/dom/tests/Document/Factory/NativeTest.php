<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license MIT
 */
namespace Essence\Dom\Document\Factory;

use PHPUnit_Framework_TestCase as TestCase;



/**
 *	Test case for Native.
 */
class NativeTest extends TestCase {

	/**
	 *
	 */
	public function testDocument() {
		$Factory = new Native();
		$Document = $Factory->document('html');

		$this->assertInstanceOf('Essence\Dom\Document', $Document);
	}
}
