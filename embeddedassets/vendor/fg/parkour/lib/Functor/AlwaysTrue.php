<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */
namespace Parkour\Functor;



/**
 *
 */
class AlwaysTrue {

	/**
	 *	This function is always true
	 *
	 *	@return boolean true
	 */
	public function __invoke() {
		return true;
	}
}
