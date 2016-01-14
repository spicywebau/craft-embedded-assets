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
class IdentityTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$Identity = new Identity();

		$this->assertEquals(2, $Identity(2));
		$this->assertEquals(4, $Identity(4, 2, 'foo'));
	}
}
