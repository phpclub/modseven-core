<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use Modseven\Tests\Support\TestCase;

class ArrFlattenTest extends TestCase
{
	/**
	 * @dataProvider flattenProvider
	 * @param array $array The array to flatten
	 * @param array $expected The expected one-dimensional array
	 */
	public function testFlatten(array $array, array $expected): void
	{
		$this->assertSame(
			$expected,
			Arr::flatten($array)
		);
	}

	/**
	 * Test a deeply nested array.
	 */
	public function testFlattenDeeplyNestedArray(): void
	{
		$array = [
			'one' => [
				'two' => [
					'three' => 'value'
				]
			]
		];

		$expected = ['value'];
		$this->assertSame($expected, Arr::flatten($array));
	}

	/**
	 * Test an array with numeric and associative keys.
	 */
	public function testFlattenWithMixedKeys(): void
	{
		$array = [
			'a' => 'A',
			0 => [
				'b' => 'B'
			],
			1 => [
				'c' => 'C'
			]
		];

		$expected = ['A', 'B', 'C'];
		$this->assertSame($expected, Arr::flatten($array));
	}

	/**
	 * @return array<int, array{array, array}>
	 */
	public static function flattenProvider(): array
	{
		return [
			// Case 1: Simple nested array. Keys will be lost.
			[
				['set' => ['one' => 'something'], 'two' => 'other'],
				['something', 'other']
			],
			// Case 2: Indexed nested array.
			[
				['A', ['B', ['C']]],
				['A', 'B', 'C']
			],
			// Case 3: Empty array.
			[
				[],
				[]
			],
			// Case 4: Array with string and numeric keys. Keys will be lost.
			[
				[
					'users' => [
						'name' => 'John',
						'id' => 1
					],
					'posts' => [
						0 => ['title' => 'Post 1'],
						1 => ['title' => 'Post 2']
					]
				],
				['John', 1, 'Post 1', 'Post 2']
			],
		];
	}
}