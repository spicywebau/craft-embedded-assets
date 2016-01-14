<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */
namespace Parkour;

use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters as ConsecutiveParameters;
use Parkour\Functor\Equal;
use Parkour\Functor\Identity;
use ReflectionClass;



/**
 *
 */
class TraverseTest extends TestCase {

	/**
	 *	Returns a closure constrained by the given values.
	 *
	 *	@see https://phpunit.de/manual/current/en/test-doubles.html#test-doubles.stubs.examples.StubTest5.php
	 *	@see https://phpunit.de/manual/current/en/test-doubles.html#test-doubles.mock-objects.examples.with-consecutive.php
	 *	@param array $values Values.
	 *	@param int $calls Number of expected calls.
	 *	@return Closure Closure.
	 */
	private function closure(array $values, $calls = null) {
		if ($calls === null) {
			$calls = count($values);
		}

		$Mock = $this->getMock('stdClass', ['method']);

		$Mocker = $Mock->expects($this->exactly($calls));
		$Mocker->method('method');
		$Mocker->will($this->returnValueMap($values));

		$with = array_map(function($arguments) {
			return array_slice($arguments, 0, -1);
		}, $values);

		$Matcher = new ConsecutiveParameters($with);
		$Mocker->getMatcher()->parametersMatcher = $Matcher;

		$Reflection = new ReflectionClass($Mock);
		$Method = $Reflection->getMethod('method');

		return $Method->getClosure($Mock);
	}

	/**
	 *
	 */
	public function testEach() {
		$data = [
			'a' => 1,
			'b' => 2
		];

		$closure = $this->closure([
			[1, 'a', null],
			[2, 'b', null]
		]);

		Traverse::each($data, $closure);
	}

	/**
	 *
	 */
	public function testMap() {
		$data = [
			'a' => 1,
			'b' => 2
		];

		$closure = $this->closure([
			[1, 'a', 2],
			[2, 'b', 4]
		]);

		$expected = [
			'a' => 2,
			'b' => 4
		];

		$this->assertEquals(
			$expected,
			Traverse::map($data, $closure)
		);
	}

	/**
	 *
	 */
	public function testMapKeys() {
		$data = [
			'a' => 1,
			'b' => 2
		];

		$closure = $this->closure([
			[1, 'a', 'c'],
			[2, 'b', 'd']
		]);

		$expected = [
			'c' => 1,
			'd' => 2
		];

		$this->assertEquals(
			$expected,
			Traverse::mapKeys($data, $closure)
		);
	}

	/**
	 *
	 */
	public function testFilter() {
		$methods = [
			'filter',
			'customFilter'
		];

		if (defined('ARRAY_FILTER_USE_BOTH')) {
			$methods[] = 'nativeFilter';
		}

		foreach ($methods as $method) {
			$closure = $this->closure([
				[1, 'a', false],
				[2, 'b', true]
			]);

			$data = [
				'a' => 1,
				'b' => 2
			];

			$expected = [
				'b' => 2
			];

			$result = call_user_func(
				['\\Parkour\\Traverse', $method],
				$data,
				$closure
			);

			$this->assertEquals($expected, $result);
		}
	}

	/**
	 *
	 */
	public function testFilterUnkeyed() {
		$EqualTwo = new Equal(2);
		$data = [1, 2, 1, 2];
		$expected = [2, 2];

		$this->assertEquals(
			$expected,
			Traverse::filter($data, $EqualTwo, false)
		);
	}

	/**
	 *
	 */
	public function testReject() {
		$data = [
			'a' => 1,
			'b' => 2
		];

		$closure = $this->closure([
			[1, 'a', false],
			[2, 'b', true]
		]);

		$expected = [
			'a' => 1
		];

		$this->assertEquals(
			$expected,
			Traverse::reject($data, $closure)
		);
	}

	/**
	 *
	 */
	public function testReduce() {
		$data = [1, 2];

		$closure = $this->closure([
			[0, 1, 0, 1],
			[1, 2, 1, 3]
		]);

		$expected = 3;

		$this->assertEquals(
			$expected,
			Traverse::reduce($data, $closure, 0)
		);
	}

	/**
	 *
	 */
	public function testFind() {
		$data = [1, 2];

		$closure = $this->closure([
			[1, 0, false],
			[2, 1, true]
		]);

		$expected = 2;

		$this->assertEquals(
			$expected,
			Traverse::find($data, $closure)
		);
	}

	/**
	 *
	 */
	public function testFindDefault() {
		$this->assertEquals(
			'default',
			Traverse::find([], function() {}, 'default')
		);
	}

	/**
	 *
	 */
	public function testFindKey() {
		$data = [1, 2];

		$closure = $this->closure([
			[1, 0, false],
			[2, 1, true]
		]);

		$expected = 1;

		$this->assertEquals(
			$expected,
			Traverse::findKey($data, $closure)
		);
	}

	/**
	 *
	 */
	public function testFindKeyDefault() {
		$this->assertEquals(
			'default',
			Traverse::findKey([], new Identity(), 'default')
		);
	}

	/**
	 *
	 */
	public function testSome() {
		$data = [1, 2];

		$closure = $this->closure([
			[1, 0, false],
			[2, 1, false]
		]);

		$this->assertFalse(Traverse::some($data, $closure));

		$closure = $this->closure([
			[1, 0, true]
		]);

		$this->assertTrue(Traverse::some($data, $closure));
	}

	/**
	 *
	 */
	public function testEvery() {
		$data = [1, 2];

		$closure = $this->closure([
			[1, 0, false]
		]);

		$this->assertFalse(Traverse::every($data, $closure));

		$closure = $this->closure([
			[1, 0, true],
			[2, 1, true]
		]);

		$this->assertTrue(Traverse::every($data, $closure));
	}
}
