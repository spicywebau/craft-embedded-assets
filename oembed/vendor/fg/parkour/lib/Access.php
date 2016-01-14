<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */
namespace Parkour;

use InvalidArgumentException;



/**
 *
 */
class Access {

	/**
	 *	Tells if there is data at the given path.
	 *
	 *	@see splitPath()
	 *	@param array $data Data.
	 *	@param array|string $path Path.
	 *	@return boolean If there is data.
	 */
	public static function has(array $data, $path) {
		$keys = self::splitPath($path);
		$current = $data;

		foreach ($keys as $key) {
			if (!isset($current[$key])) {
				return false;
			}

			$current = $current[$key];
		}

		return true;
	}

	/**
	 *	Returns the value at the given path.
	 *
	 *	@see splitPath()
	 *	@param array $data Data.
	 *	@param array|string $path Path.
	 *	@param mixed $default Default value.
	 *	@return mixed Value.
	 */
	public static function get(array $data, $path, $default = null) {
		$keys = self::splitPath($path);
		$current = $data;

		foreach ($keys as $key) {
			if (!isset($current[$key])) {
				return $default;
			}

			$current = $current[$key];
		}

		return $current;
	}

	/**
	 *	Sets data at the given path.
	 *
	 *	@see splitPath()
	 *	@param array $data Data.
	 *	@param array|string $path Path.
	 *	@param mixed $value Value.
	 *	@return mixed Updated data.
	 */
	public static function set(array $data, $path, $value) {
		$keys = self::splitPath($path);
		$current =& $data;

		foreach ($keys as $key) {
			if (!is_array($current)) {
				return $data;
			}

			if (!isset($current[$key])) {
				$current[$key] = [];
			}

			$current =& $current[$key];
		}

		$current = $value;
		return $data;
	}

	/**
	 *	Updates data at the given path.
	 *
	 *	@see splitPath()
	 *	@param array $data Data.
	 *	@param array|string $path Path.
	 *	@param callable $cb Callback to update the value.
	 *	@return mixed Updated data.
	 */
	public static function update(array $data, $path, callable $cb) {
		$keys = self::splitPath($path);
		$current =& $data;

		foreach ($keys as $key) {
			if (!isset($current[$key])) {
				return $data;
			}

			$current =& $current[$key];
		}

		$current = call_user_func($cb, $current);
		return $data;
	}

	/**
	 *	Splits a path into multiple keys.
	 *
	 *	@param array|string $path Path.
	 *	@return array Keys.
	 */
	public static function splitPath($path) {
		$keys = is_string($path)
			? array_filter(explode('.', $path))
			: $path;

		if (!is_array($keys)) {
			throw new InvalidArgumentException(
				'The path should be either an array or a string.'
			);
		}

		if (empty($keys)) {
			throw new InvalidArgumentException(
				'The path should not be empty.'
			);
		}

		return $keys;
	}
}
