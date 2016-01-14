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
class DisjunctTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$Disjunct = new Disjunct();
		$this->assertTrue($Disjunct(true, false));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$Disjunct = new Disjunct(false);
		$this->assertTrue($Disjunct(true));
	}
}
