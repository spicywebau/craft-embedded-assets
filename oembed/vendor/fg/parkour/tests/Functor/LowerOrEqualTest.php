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
class LowerOrEqualTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$LowerOrEqual = new LowerOrEqual();

		$this->assertTrue($LowerOrEqual(1, 2));
		$this->assertTrue($LowerOrEqual(2, 2));
		$this->assertFalse($LowerOrEqual(3, 2));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$LowerOrEqual = new LowerOrEqual(2);

		$this->assertTrue($LowerOrEqual(1));
		$this->assertTrue($LowerOrEqual(2));
		$this->assertFalse($LowerOrEqual(3));
	}
}
