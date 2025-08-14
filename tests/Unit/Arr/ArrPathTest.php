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

	/**
	 * @dataProvider nonExistentPathProvider
	 */
	public function testPathBreaks($array, $path, $default, $expected)
	{
		$this->assertSame($expected, \Modseven\Arr::path($array, $path, $default));
	}

	public static function nonExistentPathProvider(): array
	{
		return [
			'missing-key' => [
				['a' => 1, 'b' => 2],
				'c.d',
				'not-found',
				'not-found',
			],
			'wildcard-no-match' => [
				[
					['name' => 'John'],
					['name' => 'Jane']
				],
				'*.age',
				'not-found',
				'not-found',
			],
		];
	}


	/**
	 * Test coverage for Arr::path() wildcard with no matches (break at line 186)
	 *
	 * @link https://www.php.net/ctype_digit
	 * @link https://wiki.php.net/rfc/deprecations_php_8_1
	 */
	public function testPathWildcardNoMatch()
	{
		$array = [
			['name' => 'John'],
			['name' => 'Jane']
		];

		// '*.age' does not exist, triggers the break at line 186
		$result = \Modseven\Arr::path($array, '*.age', 'not-found');

		$this->assertSame('not-found', $result);
	}

}
