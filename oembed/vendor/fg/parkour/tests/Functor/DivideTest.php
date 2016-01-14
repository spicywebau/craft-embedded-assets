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
class DivideTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$Divide = new Divide();
		$this->assertEquals(2, $Divide(4, 2));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$Divide = new Divide(2);
		$this->assertEquals(2, $Divide(4));
	}
}
