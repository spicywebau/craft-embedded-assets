<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */
namespace Parkour;

use PHPUnit_Framework_TestCase as TestCase;



/**
 *
 */
class TransformTest extends TestCase {

	/**
	 *
	 */
	public function testCombine() {
		$users = [
			['id' => 1, 'name' => 'a'],
			['id' => 2, 'name' => 'b'],
			['id' => 3, 'name' => 'b']
		];

		$closure = function($user) {
			yield $user['name'] => $user['id'];
		};

		$expected = [
			'a' => 1,
			'b' => 3
		];

		// overwriting existing names
		$this->assertEquals(
			$expected,
			Transform::combine($users, $closure)
		);

		$expected = [
			'a' => 1,
			'b' => 2
		];

		// not overwriting existing names
		$this->assertEquals(
			$expected,
			Transform::combine($users, $closure, false)
		);
	}

	/**
	 *
	 */
	public function testNormalize() {
		$data = [
			'one',
			'two' => 'three',
			'four'
		];

		$default = 'default';

		$expected = [
			'one' => $default,
			'two' => 'three',
			'four' => $default
		];

		$this->assertEquals(
			$expected,
			Transform::normalize($data, $default)
		);
	}

	/**
	 *
	 */
	public function testReindex() {
		$data = ['foo' => 'bar'];
		$map = ['foo' => 'baz'];

		$expected = [
			'foo' => 'bar',
			'baz' => 'bar'
		];

		$this->assertEquals(
			$expected,
			Transform::reindex($data, $map)
		);

		$expected = ['baz' => 'bar'];

		$this->assertEquals(
			$expected,
			Transform::reindex($data, $map, false)
		);
	}

	/**
	 *
	 */
	public function testMerge() {
		$first = [
			'one' => 1,
			'two' => 2,
			'three' => [
				'four' => 4,
				'five' => 5
			]
		];

		$second = [
			'two' => 'two',
			'three' => [
				'four' => 'four'
			]
		];

		$expected = [
			'one' => 1,
			'two' => 'two',
			'three' => [
				'four' => 'four',
				'five' => 5
			]
		];

		$this->assertEquals(
			$expected,
			Transform::merge($first, $second)
		);
	}
}
