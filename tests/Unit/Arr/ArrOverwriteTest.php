<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use Modseven\Tests\Support\TestCase;

class ArrOverwriteTest extends TestCase
{
	/**
	 * @dataProvider overwriteProvider
	 * @param array $expected Expected array state after overwriting
	 * @param array $array1 The array to overwrite
	 * @param array $array2 The array with new values
	 * @param array ...$arrays Additional arrays to overwrite from
	 */
	public function testOverwrite(array $expected, array $array1, array $array2, array ...$arrays): void
	{
		$this->assertSame(
			$expected,
			Arr::overwrite($array1, $array2, ...$arrays)
		);
	}

	/**
	 * Test overwriting with a single array.
	 */
	public function testOverwriteSingleArray(): void
	{
		$array1 = ['name' => 'John', 'age' => 30];
		$array2 = ['age' => 35, 'city' => 'New York'];

		// 'age' is overwritten, 'name' remains, 'city' is ignored
		$expected = ['name' => 'John', 'age' => 35];
		$this->assertSame($expected, Arr::overwrite($array1, $array2));
	}

	/**
	 * Test overwriting an indexed array.
	 */
	public function testOverwriteIndexedArray(): void
	{
		$array1 = ['apple', 'banana', 'cherry'];
		$array2 = ['date', 'elderberry'];

		// 'apple' becomes 'date', 'banana' becomes 'elderberry', 'cherry' remains
		$expected = ['date', 'elderberry', 'cherry'];
		$this->assertSame($expected, Arr::overwrite($array1, $array2));
	}

	/**
	 * Test overwriting with a different data type.
	 */
	public function testOverwriteWithDifferentDataType(): void
	{
		$array1 = ['value' => 'string'];
		$array2 = ['value' => 123];

		$expected = ['value' => 123];
		$this->assertSame($expected, Arr::overwrite($array1, $array2));
	}

	/**
	 * @return array<int, array{array, array, array, array}>
	 */
	public static function overwriteProvider(): array
	{
		return [
			// Case 1: Simple overwrite with three arrays
			[
				['name' => 'Henry', 'mood' => 'tired', 'food' => 'waffles', 'sport' => 'checkers'],
				['name' => 'John', 'mood' => 'bored', 'food' => 'bacon', 'sport' => 'checkers'],
				['name' => 'Matt', 'mood' => 'tired', 'food' => 'waffles'],
				['name' => 'Henry', 'age' => 18],
			],
			// Case 2: Indexed arrays
			[
				['a', 'z', 'c', 'd'],
				['a', 'b', 'c', 'd'],
				[1 => 'z'],
				[]
			],
			// Case 3: Overwriting a nested array with a simple value
			[
				['config' => 'new_value'],
				['config' => ['foo' => 'bar']],
				['config' => 'new_value'],
				[]
			],
			// Case 4: Overwriting a simple value with a nested array
			[
				['config' => ['foo' => 'bar']],
				['config' => 'old_value'],
				['config' => ['foo' => 'bar']],
				[]
			]
		];
	}
}