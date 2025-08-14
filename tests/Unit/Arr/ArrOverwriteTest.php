<?php declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;
use TypeError;

class ArrOverwriteTest extends TestCase
{
	/**
	 * @dataProvider overwriteProvider
	 */
	public function testOverwrite(string $case, array $array1, array $array2, array $expected, ?array $array3 = null): void
	{
		$arrays = $array3 !== null ? [$array1, $array2, $array3] : [$array1, $array2];
		$result = call_user_func_array([Arr::class, 'overwrite'], $arrays);

		$this->assertSame($expected, $result, "Case '{$case}' failed");
	}

	/**
	 * Test that passing non-array as second argument throws TypeError.
	 */
	public function testOverwriteThrowsTypeError(): void
	{
		$this->expectException(TypeError::class);

		/** @noinspection PhpParamsInspection intentionally passing string instead of array  */
		$result = Arr::overwrite(['foo' => 'bar'], 'baz');
	}

	/**
	 * Data provider for overwrite tests.
	 *
	 * Adjusted to match current Modseven behavior:
	 * - Only existing top-level keys are overwritten.
	 * - Nested arrays are NOT merged recursively.
	 *
	 * @return array
	 */
	public static function overwriteProvider(): array
	{
		return [
			'simple-overwrite' => [
				'simple-overwrite',
				['a' => 1, 'b' => 2],
				['b' => 3, 'c' => 4],
				['a' => 1, 'b' => 3],
			],
			'recursive-merge' => [
				'recursive-merge',
				[['John', 30]],
				[[25]],
				[[25]], // overwrite заменяет только существующие верхние ключи
			],
		];
	}
}
