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
class NotEqualTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$NotEqual = new NotEqual();
		$this->assertTrue($NotEqual(1, 2));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$NotEqual = new NotEqual(2);
		$this->assertTrue($NotEqual(1));
	}
}
