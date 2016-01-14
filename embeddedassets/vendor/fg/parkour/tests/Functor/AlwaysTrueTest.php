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
class AlwaysTrueTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$AlwaysTrue = new AlwaysTrue();

		$this->assertTrue($AlwaysTrue(2));
		$this->assertTrue($AlwaysTrue(4, 2, 'foo'));
	}
}
