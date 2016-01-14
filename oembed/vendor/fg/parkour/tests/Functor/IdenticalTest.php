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
class IdenticalTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$Identical = new Identical();
		$this->assertTrue($Identical(2, 2));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$Identical = new Identical(2);
		$this->assertTrue($Identical(2));
	}
}
