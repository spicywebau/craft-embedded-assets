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
class NotIdenticalTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$NotIdentical = new NotIdentical();
		$this->assertTrue($NotIdentical('2', 2));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$NotIdentical = new NotIdentical(2);
		$this->assertTrue($NotIdentical('2'));
	}
}
