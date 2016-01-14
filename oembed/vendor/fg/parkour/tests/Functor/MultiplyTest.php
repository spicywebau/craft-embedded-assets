<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */
namespace Parkour\Functor;

use PHPUnit_Framework_TestCase as TestCase;



/**
 *
 */
class MultiplyTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$Multiply = new Multiply();
		$this->assertEquals(4, $Multiply(2, 2));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$Multiply = new Multiply(2);
		$this->assertEquals(4, $Multiply(2));
	}
}
