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
class ExecuteTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$Execute = new Execute();
		$addTwo = function($value) {
			return $value + 2;
		};

		$this->assertEquals(4, $Execute(2, $addTwo));
	}
}
