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
class GreaterTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$Greater = new Greater();

		$this->assertTrue($Greater(4, 2));
		$this->assertFalse($Greater(2, 2));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$Greater = new Greater(2);

		$this->assertTrue($Greater(4));
		$this->assertFalse($Greater(2));
	}
}
