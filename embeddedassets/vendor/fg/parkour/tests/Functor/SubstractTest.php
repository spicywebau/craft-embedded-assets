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
class SubstractTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$Substract = new Substract();
		$this->assertEquals(2, $Substract(4, 2));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$Substract = new Substract(2);
		$this->assertEquals(2, $Substract(4));
	}
}
