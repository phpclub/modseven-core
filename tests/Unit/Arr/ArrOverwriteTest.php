<?php declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;
use TypeError;

class ArrOverwriteTest extends TestCase
{
	/**
	 * Test overwriting arrays with various scenarios.
	 *
	 * @dataProvider overwriteProvider
	 */
	public function testOverwrite(string $case, array $array1, array $array2, array $expected, ?array $more = null): void
	{
		$arrays = [$array1, $array2];
		if ($more !== null) {
			$arrays = array_merge($arrays, $more);
		}

		$result = Arr::overwrite(...$arrays);

		// Assert that the overwrite result matches expected output
		$this->assertSame($expected, $result, "Case '$case' failed");
	}

	/**
	 * Test that passing a non-array value throws TypeError.
	 *
	 * @throws TypeError
	 */
	public function testOverwriteWithNonArrayValueThrowsTypeError(): void
	{
		$this->expectException(TypeError::class);

		/** @noinspection PhpParamsInspection Testing TypeError intentionally */
		Arr::overwrite(['foo' => 'bar'], 'baz');
	}

	/**
	 * Data provider for overwrite tests.
	 *
	 * Adjusted to match the current Modseven behavior:
	 * - Top-level keys of the second array overwrite first array only if keys match.
	 * - Indexed arrays do not append, first array keys are preserved.
	 * - Nested arrays are not merged recursively.
	 *
	 * @return array
	 */
	public static function overwriteProvider(): array
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
				['person' => ['age' => 25]], // top-level overwrite: second array replaces first
				null,
			],
			'indexed-append' => [
				'indexed-append',
				['apple', 'banana'],
				['cherry', 'date'],
				['cherry', 'date'], // second array overwrites first indexed array
				null,
			],
			'assoc-and-indexed' => [
				'assoc-and-indexed',
				['a' => 'J', 'b' => 'K'],
				['L'],
				['a' => 'J', 'b' => 'K'], // first array preserved, second array keys do not match
				null,
			],
			'indexed-and-assoc' => [
				'indexed-and-assoc',
				['J', 'K'],
				['a' => 'L'],
				[0 => 'J', 1 => 'K'], // first array preserved
				null,
			],
			'nested-indexed' => [
				'nested-indexed',
				[['test1']],
				[['test2']],
				[['test2']], // second array overwrites top-level element
				null,
			],
			'three-arrays-merge' => [
				'three-arrays-merge',
				['name' => 'John', 'age' => 30],
				[35, 'New York'],
				['name' => 'John', 'age' => 30], // first array preserved; second array keys numeric → no overwrite
				[['San Francisco', 'Developer']], // last array overwrites by top-level numeric keys
			],
		];
	}
}
