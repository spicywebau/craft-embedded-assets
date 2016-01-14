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
class GreaterOrEqualTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$GreaterOrEqual = new GreaterOrEqual();

		$this->assertTrue($GreaterOrEqual(3, 2));
		$this->assertTrue($GreaterOrEqual(2, 2));
		$this->assertFalse($GreaterOrEqual(1, 2));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$GreaterOrEqual = new GreaterOrEqual(2);

		$this->assertTrue($GreaterOrEqual(3));
		$this->assertTrue($GreaterOrEqual(2));
		$this->assertFalse($GreaterOrEqual(1));
	}
}
