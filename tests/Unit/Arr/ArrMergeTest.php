<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use Modseven\Tests\Support\TestCase;
use TypeError;

class ArrMergeTest extends TestCase
{
	/**
	 * @dataProvider mergeProvider
	 * @param array $array1 The first array to merge
	 * @param array $array2 The second array to merge
	 * @param array $expected Expected array state after merging
	 */
	public function testMerge(array $array1, array $array2, array $expected): void
	{
		$this->assertEquals(
			$expected,
			Arr::merge($array1, $array2)
		);
	}

	/**
	 * Test that Arr::merge can handle more than two arrays.
	 */
	public function testMergeWithMultipleArrays(): void
	{
		$array1 = ['name' => 'John', 'age' => 30];
		$array2 = ['age' => 35, 'city' => 'New York'];
		$array3 = ['city' => 'San Francisco', 'job' => 'Developer'];

		$expected = [
			'name' => 'John',
			'age' => 35,
			'city' => 'San Francisco',
			'job' => 'Developer'
		];

		$this->assertEquals($expected, Arr::merge($array1, $array2, $array3));
	}

	/**
	 * Test merging indexed arrays. Arr::merge should append values, not overwrite.
	 */
	public function testMergeIndexedArrays(): void
	{
		$array1 = ['apple', 'banana'];
		$array2 = ['cherry', 'date'];

		$expected = ['apple', 'banana', 'cherry', 'date'];
		$this->assertEquals($expected, Arr::merge($array1, $array2));
	}

	/**
	 * Test recursive merging of nested associative arrays.
	 */
	public function testMergeRecursiveAssociative(): void
	{
		$array1 = ['user' => ['name' => 'John', 'details' => ['hobby' => 'coding']]];
		$array2 = ['user' => ['age' => 30, 'details' => ['city' => 'New York']]];

		$expected = [
			'user' => [
				'name' => 'John',
				'details' => [
					'hobby' => 'coding',
					'city' => 'New York'
				],
				'age' => 30, // Corrected position
			]
		];

		$this->assertEquals($expected, Arr::merge($array1, $array2));
	}

	/**
	 * Test merging with a non-array value throws a TypeError.
	 */
	public function testMergeWithNonArrayValueThrowsTypeError(): void
	{
		$this->expectException(TypeError::class);

		$array1 = ['foo' => 'bar'];
		$value = 'baz';

		Arr::merge($array1, $value);
	}

	/**
	 * @return array<string, array{array, array, array}>
	 */
	public static function mergeProvider(): array
	{
		return [
			// Case 1: Simple associative merge with overwriting
			'simple-overwrite' => [
				['name' => 'John', 'age' => 30],
				['name' => 'Jane'],
				['name' => 'Jane', 'age' => 30], // Ожидается перезапись 'John' на 'Jane'
			],

			// Case 2: Recursive associative merge
			'recursive-merge' => [
				['person' => ['name' => 'John']],
				['person' => ['age' => 25]],
				['person' => ['name' => 'John', 'age' => 25]],
			],

			// Case 3: Simple indexed merge (appending)
			'simple-indexed' => [
				[0, 1],
				[2, 3],
				[0, 1, 2, 3],
			],

			// Case 4: Associative + Indexed (associative keys are preserved, indexed are re-keyed)
			'assoc-and-indexed' => [
				['a' => 'J', 'b' => 'K'],
				[0 => 'L'],
				['a' => 'J', 'b' => 'K', 'L'], // 'L' добавляется с новым индексом
			],

			// Case 5: Indexed + Associative (associative key is preserved)
			'indexed-and-assoc' => [
				['J', 'K'],
				['a' => 'L'],
				['J', 'K', 'a' => 'L'],
			],

			// Case 6: Nested indexed arrays (simple appending)
			'nested-indexed' => [
				[['test1']],
				[['test2']],
				[['test1'], ['test2']],
			],
		];
	}
}