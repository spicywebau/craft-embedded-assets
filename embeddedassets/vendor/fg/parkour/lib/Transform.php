<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */
namespace Parkour;



/**
 *
 */
class Transform {

	/**
	 *	Indexes an array depending on the values it contains.
	 *
	 *	@param array $data Data.
	 *	@param callable $cb Function to combine values.
	 *	@param boolean $overwrite Should duplicate keys be overwritten ?
	 *	@return array Indexed values.
	 */
	public static function combine(array $data, callable $cb, $overwrite = true) {
		$combined = [];

		foreach ($data as $key => $value) {
			$Combinator = call_user_func($cb, $value, $key);
			$index = $Combinator->key();

			if ($overwrite || !isset($combined[$index])) {
				$combined[$index] = $Combinator->current();
			}
		}

		return $combined;
	}

	/**
	 *	Makes every value that is numerically indexed a key, given $default
	 *	as value.
	 *
	 *	@param array $data Data.
	 *	@param mixed $default Default value.
	 *	@return array Normalized values.
	 */
	public static function normalize(array $data, $default) {
		$normalized = [];

		foreach ($data as $key => $value) {
			if (is_numeric($key)) {
				$key = $value;
				$value = $default;
			}

			$normalized[$key] = $value;
		}

		return $normalized;
	}

	/**
	 *	Reindexes a list of values.
	 *
	 *	@param array $data Data.
	 *	@param array $map An map of correspondances of the form
	 *		['currentIndex' => 'newIndex'].
	 *	@return boolean $keepUnmapped Whether or not to keep keys that are not
	 *		remapped.
	 *	@return array Reindexed values.
	 */
	public static function reindex(array $data, array $map, $keepUnmapped = true) {
		$reindexed = $keepUnmapped
			? $data
			: [];

		foreach ($map as $from => $to) {
			if (isset($data[$from])) {
				$reindexed[$to] = $data[$from];
			}
		}

		return $reindexed;
	}

	/**
	 *	Merges two arrays recursively.
	 *
	 *	@param array $first Original data.
	 *	@param array $second Data to be merged.
	 *	@return array Merged data.
	 */
	public static function merge(array $first, array $second) {
		foreach ($second as $key => $value) {
			$shouldBeMerged = (
				isset($first[$key])
				&& is_array($first[$key])
				&& is_array($value)
			);

			$first[$key] = $shouldBeMerged
				? self::merge($first[$key], $value)
				: $value;
		}

		return $first;
	}
}
