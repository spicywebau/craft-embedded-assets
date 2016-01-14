<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license MIT
 */
namespace Essence\Dom\Document\Factory;

use Essence\Dom\Document\Factory;
use Essence\Dom\Document\Native as Document;



/**
 *	A factory for native documents.
 */
class Native {

	/**
	 *	{@inheritDoc}
	 */
	public function document($html) {
		return new Document($html);
	}
}
