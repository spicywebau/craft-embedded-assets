<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license MIT
 */
namespace Essence\Dom\Document;

use Essence\Dom\Document;



/**
 *	A factory for documents.
 */
interface Factory {

	/**
	 *	Builds and returns a document.
	 *
	 *	@param string $html HTML source.
	 *	@return Document Document.
	 */
	public function document($html);

}
