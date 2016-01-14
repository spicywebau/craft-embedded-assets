<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */
namespace Parkour;



/**
 *	A collection of utilities to manipulate arrays.
 */
class Traverse {

	/**
	 *	Iterates over the given data.
	 *
	 *	@param array $data Data.
	 *	@param callable $cb Function that receives values.
	 */
	public static function each(array $data, callable $cb) {
		array_walk($data, $cb);
	}

	/**
	 *	Updates each of the given values.
	 *
	 *	@param array $data Data.
	 *	@param callable $cb Function to map values.
	 *	@return array Mapped data.
	 */
	public static function map(array $data, callable $cb) {
		foreach ($data as $key => $value) {
			$data[$key] = call_user_func($cb, $value, $key);
		}

		return $data;
	}

	/**
	 *	Updates the keys each of the given data.
	 *
	 *	@param array $data Data.
	 *	@param callable $cb Function to map keys.
	 *	@return array Mapped data.
	 */
	public static function mapKeys(array $data, callable $cb) {
		$mapped = [];

		foreach ($data as $key => $value) {
			$mappedKey = call_user_func($cb, $value, $key);
			$mapped[$mappedKey] = $value;
		}

		return $mapped;
	}

	/**
	 *	Filters each of the given values through a function.
	 *
	 *	@param array $data Data.
	 *	@param callable $cb Function to filter values.
	 *	@return array Filtered data.
	 */
	public static function filter(array $data, callable $cb, $keyed = true) {
		return defined('ARRAY_FILTER_USE_BOTH')
			? self::nativeFilter($data, $cb, $keyed)
			: self::customFilter($data, $cb, $keyed);
	}

	/**
	 *	@see filter()
	 */
	public static function nativeFilter(array $data, callable $cb, $keyed = true) {
		$filtered = array_filter($data, $cb, ARRAY_FILTER_USE_BOTH);

		return $keyed
			? $filtered
			: array_values($filtered);
	}

	/**
	 *	@see filter()
	 */
	public static function customFilter(array $data, callable $cb, $keyed = true) {
		$filtered = [];

		foreach ($data as $key => $value) {
			if (call_user_func($cb, $value, $key)) {
				if ($keyed) {
					$filtered[$key] = $value;
				} else {
					$filtered[] = $value;
				}
			}
		}

		return $filtered;
	}

	/**
	 *	The opposite of filter().
	 *
	 *	@param array $data Data.
	 *	@param callable $cb Function to filter values.
	 *	@return array Filtered data.
	 */
	public static function reject(array $data, callable $cb, $keyed = true) {
		return self::filter($data, function($value, $key) use ($cb) {
			return !call_user_func($cb, $value, $key);
		}, $keyed);
	}

	/**
	 *	Boils down a list of values to a single value.
	 *
	 *	@param array $data Data.
	 *	@param callable $cb Function to reduce values.
	 *	@param mixed $memo Initial value.
	 *	@return mixed Result.
	 */
	public static function reduce(array $data, callable $cb, $memo) {
		foreach ($data as $key => $value) {
			$memo = call_user_func($cb, $memo, $value, $key);
		}

		return $memo;
	}

	/**
	 *	Finds a value in the given data.
	 *
	 *	@param array $data Data.
	 *	@param callable $cb Function to find value.
	 *	@param mixed $default Default value.
	 *	@return mixed Value.
	 */
	public static function find(array $data, callable $cb, $default = null) {
		$key = self::findKey($data, $cb);

		return ($key === null)
			? $default
			: $data[$key];
	}

	/**
	 *	Finds a value in the given data and returns its key.
	 *
	 *	@param array $data Data.
	 *	@param callable $cb Function to find value.
	 *	@param mixed $default Default value.
	 *	@return int|string Key.
	 */
	public static function findKey(array $data, callable $cb, $default = null) {
		foreach ($data as $key => $value) {
			if (call_user_func($cb, $value, $key)) {
				return $key;
			}
		}

		return $default;
	}

	/**
	 *	Returns true if some elements of the given data passes
	 *	a thruth test.
	 *
	 *	@param array $data Data.
	 *	@param callable $cb Test.
	 *	@return boolean If some elements passes the test.
	 */
	public static function some(array $data, callable $cb) {
		foreach ($data as $key => $value) {
			if (call_user_func($cb, $value, $key)) {
				return true;
			}
		}

		return false;
	}

	/**
	 *	Returns true if every element of the given data passes
	 *	a thruth test.
	 *
	 *	@param array $data Data.
	 *	@param callable $cb Test.
	 *	@return boolean If every element passes the test.
	 */
	public static function every(array $data, callable $cb) {
		foreach ($data as $key => $value) {
			if (!call_user_func($cb, $value, $key)) {
				return false;
			}
		}

		return true;
	}
}
