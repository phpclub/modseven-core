<?php

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;

class ArrMergeTest extends TestCase
{
	/**
	 * Data provider for testMerge
	 *
	 * Covers:
	 * - Recursive merge of associative arrays
	 * - Overwrite scalar values
	 * - Append unique numeric values
	 * - Variadic merge
	 * - Mixed numeric and string keys
	 * - Empty arrays
	 *
	 * @return array
	 */
	public static function providerMerge(): array
	{
		return [
			'simple-overwrite' => [
				['a' => 1, 'b' => 2],
				['b' => 3, 'c' => 4],
				['a' => 1, 'b' => 3, 'c' => 4],
			],
			'recursive-merge' => [
				['person' => ['name' => 'John', 'age' => 25]],
				['person' => ['age' => 30, 'city' => 'NY']],
				['person' => ['name' => 'John', 'age' => 30, 'city' => 'NY']],
			],
			'numeric-append' => [
				[1, 2],
				[2, 3, 4],
				[1, 2, 3, 4],
			],
			'nested-numeric-recursive' => [
				['group' => [1, 2]],
				['group' => [2, 3]],
				['group' => [1, 2, 3]],
			],
			'variadic-merge' => [
				['a' => 1],
				['b' => 2],
				['c' => 3],
				['a' => 1, 'b' => 2, 'c' => 3],
			],
			'empty-first-array' => [
				[],
				['a' => 1],
				['a' => 1],
			],
			'empty-second-array' => [
				['a' => 1],
				[],
				['a' => 1],
			],
			'mixed-numeric-string-keys' => [
				['a' => 1, 0 => 'x'],
				[0 => 'y', 'b' => 2],
				['a' => 1, 0 => 'y', 'b' => 2],
			],
			'multi-level-recursive' => [
				['x' => ['y' => ['z' => 1]]],
				['x' => ['y' => ['w' => 2]]],
				['x' => ['y' => ['z' => 1, 'w' => 2]]],
			],
		];
	}

	/**
	 * @dataProvider providerMerge
	 */
	public function testMerge(...$args)
	{
		$expected = array_pop($args);
		$this->assertSame($expected, Arr::merge(...$args));
	}
}
