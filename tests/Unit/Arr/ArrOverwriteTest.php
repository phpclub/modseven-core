<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrOverwriteTest
 *
 * Tests Arr::overwrite method with multiple scenarios.
 *
 * @package Modseven\Tests\Unit\Arr
 * @link https://github.com/phpclub/modseven-core
 */
class ArrOverwriteTest extends TestCase
{
	/**
	 * Data provider for Arr::overwrite tests.
	 *
	 * Covers:
	 * - empty first array
	 * - mixed keys (numeric and string)
	 * - recursive merge
	 * - multiple arrays (variadic arguments)
	 *
	 * @return array<string, array>
	 */
	public static function providerOverwrite(): array
	{
		return [
			'empty-first-array' => [
				[],                          // base array
				['a' => 1],                  // overwrite array
				['a' => 1],                  // expected result
			],
			'mixed-keys' => [
				['a' => 1, 'b' => 2],
				['b' => 3, 0 => 4],
				['a' => 1, 'b' => 3, 0 => 4],
			],
			'recursive-merge' => [
				['person' => ['name' => 'John', 'age' => 25]],
				['person' => ['age' => 30]],
				['person' => ['name' => 'John', 'age' => 30]],
			],
			'multiple-arrays' => [
				['a' => 1],
				['b' => 2],
				['c' => 3],
				['a' => 1, 'b' => 2, 'c' => 3],
			],
			'variadic-merge' => [
				['a' => 1],
				['b' => 2],
				['c' => 3],
				['d' => 4],
				['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
			],
		];
	}

	/**
	 * @dataProvider providerOverwrite
	 *
	 * @param array $array1 Base array
	 * @param array ...$arrays Arrays to overwrite into the base
	 */
	public function testOverwrite(array $array1, array ...$arrays): void
	{
		$expected = array_pop($arrays); // last element of provider array is expected result
		$this->assertSame($expected, Arr::overwrite($array1, ...$arrays));
	}
}
