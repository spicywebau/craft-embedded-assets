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
class AccessTest extends TestCase {

	/**
	 *
	 */
	public function testHas() {
		$data = [
			'a' => 1,
			'b' => [
				'c' => 2,
				'd' => [
					'e' => 3
				]
			]
		];

		$this->assertTrue(Access::has($data, 'a'));
		$this->assertTrue(Access::has($data, 'b.d.e'));
		$this->assertFalse(Access::has($data, 'a.b.c'));
	}

	/**
	 *
	 */
	public function testGet() {
		$data = [
			'a' => 1,
			'b' => [
				'c' => 2,
				'd' => [
					'e' => 3
				]
			]
		];

		$this->assertNull(Access::get($data, 'a.b.c'));
		$this->assertEquals(1, Access::get($data, 'a'));
		$this->assertEquals(3, Access::get($data, 'b.d.e'));
		$this->assertEquals('z', Access::get($data, 'a.b.c', 'z'));
	}

	/**
	 *
	 */
	public function testSet() {
		$data = [
			'a' => 1,
			'b' => [
				'c' => 2,
				'd' => [
					'e' => 3
				]
			]
		];

		$expected = [
			'a' => 'a',
			'b' => [
				'c' => 2,
				'd' => [
					'e' => 'e'
				],
				'f' => [
					'g' => 'g'
				]
			]
		];

		$result = Access::set($data, 'a', 'a');
		$result = Access::set($result, 'b.d.e', 'e');
		$result = Access::set($result, 'b.f.g', 'g');
		$result = Access::set($result, 'a.z', 'z');

		$this->assertEquals($expected, $result);
	}

	/**
	 *
	 */
	public function testUpdate() {
		$data = [
			'a' => 1,
			'b' => [
				'c' => 2,
				'd' => [
					'e' => 3
				]
			]
		];

		$expected = [
			'a' => 2,
			'b' => [
				'c' => 2,
				'd' => [
					'e' => 4
				]
			]
		];

		$increment = function($value) {
			return $value + 1;
		};

		$result = Access::update($data, 'a', $increment);
		$result = Access::update($result, 'z', $increment);
		$result = Access::update($result, 'b.d.e', $increment);

		$this->assertEquals($expected, $result);
	}

	/**
	 *
	 */
	public function testSplitPath() {
		$this->assertEquals(
			['a', 'b'],
			Access::splitPath(['a', 'b'])
		);
	}

	/**
	 *
	 */
	public function testSplitDottedPath() {
		$this->assertEquals(
			['a', 'b'],
			Access::splitPath('a.b')
		);
	}

	/**
	 *
	 */
	public function testSplitEmptyPath() {
		$this->setExpectedException('InvalidArgumentException');
		Access::splitPath([]);
	}

	/**
	 *
	 */
	public function testSplitEmptyDottedPath() {
		$this->setExpectedException('InvalidArgumentException');
		Access::splitPath('');
	}

	/**
	 *
	 */
	public function testSplitInvalidPath() {
		$this->setExpectedException('InvalidArgumentException');
		Access::splitPath(12);
	}
}
