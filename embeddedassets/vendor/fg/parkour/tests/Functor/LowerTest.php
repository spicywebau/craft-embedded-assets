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
class LowerTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$Lower = new Lower();

		$this->assertTrue($Lower(1, 2));
		$this->assertFalse($Lower(2, 2));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$Lower = new Lower(2);

		$this->assertTrue($Lower(1));
		$this->assertFalse($Lower(2));
	}
}
