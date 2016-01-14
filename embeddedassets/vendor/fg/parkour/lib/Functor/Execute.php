<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */
namespace Parkour\Functor;



/**
 *
 */
class Execute {

	/**
	 *	Executes a callback on a value.
	 *
	 *	@param mixed $value Value.
	 *	@param callable $cb Callback.
	 *	@return mixed Result.
	 */
	public function __invoke($value, callable $cb) {
		return $cb($value);
	}
}
