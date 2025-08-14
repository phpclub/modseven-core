<?php

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;

class ArrPathTest extends TestCase
{
	/**
	 * @dataProvider pathProvider
	 */
	public function testPath($input, $path, $default, $expected)
	{
		$this->assertSame($expected, Arr::path($input, $path, $default));
	}

	public static function pathProvider()
	{
		return [
			// Non-array inputs return default
			['this-is-not-an-array', 'any.path', 'default-value', 'default-value'],
			[null, 'any.path', 123, 123],
			[42, 'any.path', 'fallback', 'fallback'],
			[3.14, 'some.path', [], []],
			[new \stdClass(), 'a', 'not-found', 'not-found'],

			// Simple associative array
			[['a' => 1, 'b' => 2], 'a', null, 1],
			[['a' => 1, 'b' => 2], 'b', null, 2],
			[['a' => 1, 'b' => 2], 'c', 'missing', 'missing'],

			// Nested array
			[['person' => ['name' => 'John', 'age' => 30]], 'person.name', null, 'John'],
			[['person' => ['name' => 'John', 'age' => 30]], 'person.age', null, 30],
			[['person' => ['name' => 'John', 'age' => 30]], 'person.city', 'NY', 'NY'],

			// Wildcard *
			[
				[
					['name' => 'John', 'age' => 30],
					['name' => 'Jane', 'age' => 25]
				],
				'*.name',
				[],
				['John', 'Jane']
			],

			// Numeric keys
			[[10, 20, 30], 1, null, 20],
			[[10, 20, 30], '2', null, 30],

			// Path as array of keys
			[['person' => ['name' => 'John', 'age' => 30]], ['person', 'age'], null, 30],
		];
	}
}
