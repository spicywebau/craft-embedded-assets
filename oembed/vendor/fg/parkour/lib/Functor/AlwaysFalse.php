<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */
namespace Parkour\Functor;



/**
 *
 */
class AlwaysFalse {

	/**
	 *	This function is always false
	 *
	 *	@return boolean false
	 */
	public function __invoke() {
		return false;
	}
}
