<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */
namespace Parkour;



/**
 *
 */
abstract class Functor {

	/**
	 *
	 */
	private $hasValue = false;

	/**
	 *
	 */
	private $value = null;

	/**
	 *
	 */
	public function __construct() {
		$args = func_get_args();

		if (count($args)) {
			$this->value = array_shift($args);
			$this->hasValue = true;
		}
	}

	/**
	 *
	 */
	public function __invoke($first, $second = null) {
		return $this->invoke(
			$first,
			$this->hasValue
				? $this->value
				: $second
		);
	}

	/**
	 *
	 */
	abstract protected function invoke($first, $second);

}
