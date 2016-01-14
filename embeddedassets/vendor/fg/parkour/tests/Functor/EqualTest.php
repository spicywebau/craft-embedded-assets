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
class EqualTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$Equal = new Equal();
		$this->assertTrue($Equal('2', 2));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$Equal = new Equal(2);
		$this->assertTrue($Equal('2'));
	}
}
