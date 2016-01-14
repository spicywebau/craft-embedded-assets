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
class AddTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$Add = new Add();
		$this->assertEquals(4, $Add(2, 2));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$Add = new Add(2);
		$this->assertEquals(4, $Add(2));
	}
}
