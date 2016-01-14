<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license MIT
 */
namespace Essence\Http;



/**
 *	Handles HTTP related operations.
 */
interface Client {

	/**
	 *	Retrieves contents from the given URL.
	 *
	 *	@param string $url The URL fo fetch contents from.
	 *	@return string The contents.
	 *	@throws Essence\Http\Exception
	 */
	public function get($url);



	/**
	 *	Sets the user agent for HTTP requests.
	 *
	 *	@param string $agent User agent.
	 */
	public function setUserAgent($agent);

}
