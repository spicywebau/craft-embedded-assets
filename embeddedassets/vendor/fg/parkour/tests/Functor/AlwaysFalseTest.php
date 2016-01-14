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
class AlwaysFalseTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$AlwaysFalse = new AlwaysFalse();

		$this->assertFalse($AlwaysFalse(2));
		$this->assertFalse($AlwaysFalse(4, 2, 'foo'));
	}
}
