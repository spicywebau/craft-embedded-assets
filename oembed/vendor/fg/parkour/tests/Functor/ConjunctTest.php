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
class ConjunctTest extends TestCase {

	/**
	 *
	 */
	public function testInvoke() {
		$Conjunct = new Conjunct();
		$this->assertFalse($Conjunct(true, false));
	}

	/**
	 *
	 */
	public function testInvokeCurried() {
		$Conjunct = new Conjunct(false);
		$this->assertFalse($Conjunct(true));
	}
}
