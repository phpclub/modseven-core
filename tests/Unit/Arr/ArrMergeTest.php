<?php declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;
use TypeError;

class ArrMergeTest extends TestCase
{
	/**
	 * Test merging multiple arrays with various scenarios.
	 *
	 * @dataProvider providerMerge
	 */
	public function testMerge(string $case, array $array1, array $array2, array $expected, ?array $more = null): void
	{
		// Merge two or more arrays
		$arrays = [$array1, $array2];
		if ($more !== null) {
			$arrays = array_merge($arrays, $more);
		}

		$result = Arr::merge(...$arrays);

		// Assert merged array matches expected result
		$this->assertSame($expected, $result, "Case '$case' failed");
	}

	/**
	 * Test that passing a non-array value throws TypeError.
	 *
	 * @throws TypeError
	 */
	public function testMergeWithNonArrayValueThrowsTypeError(): void
	{
		$this->expectException(TypeError::class);

		// Passing string instead of array should throw TypeError
		/** @noinspection PhpParamsInspection */
		Arr::merge(['foo' => 'bar'], 'baz');
	}

	/**
	 * Provider for testMerge, covering multiple merge scenarios.
	 *
	 * @return array
	 */
	public static function providerMerge(): array
	{
		return [
			'simple-overwrite' => [
				'simple-overwrite',
				['name' => 'John', 'age' => 30],
				['name' => 'Jane'],
				['name' => 'Jane', 'age' => 30],
				null,
			],
			'recursive-merge' => [
				'recursive-merge',
				['person' => ['name' => 'John']],
				['person' => ['age' => 25]],
				['person' => ['name' => 'John', 'age' => 25]],
				null,
			],
			'indexed-append' => [
				'indexed-append',
				['apple', 'banana'],
				['cherry', 'date'],
				['apple', 'banana', 'cherry', 'date'],
				null,
			],
			'assoc-and-indexed' => [
				'assoc-and-indexed',
				['a' => 'J', 'b' => 'K'],
				['L'],
				['a' => 'J', 'b' => 'K', 'L'],
				null,
			],
			'indexed-and-assoc' => [
				'indexed-and-assoc',
				['J', 'K'],
				['a' => 'L'],
				['J', 'K', 'a' => 'L'],
				null,
			],
			'nested-indexed' => [
				'nested-indexed',
				[['test1']],
				[['test2']],
				[['test1'], ['test2']],
				null,
			],
			'three-arrays-merge' => [
				'three-arrays-merge',
				['name' => 'John', 'age' => 30],
				['age' => 35, 'city' => 'New York'],
				['name' => 'John', 'age' => 35, 'city' => 'San Francisco', 'job' => 'Developer'],
				[['city' => 'San Francisco', 'job' => 'Developer']],
			],
		];
	}
}
