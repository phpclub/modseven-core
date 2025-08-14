<?php declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;
use TypeError;

class ArrFlattenTest extends TestCase
{
	/**
	 * @dataProvider flattenProvider
	 */
	public function testFlatten(array $input, array $expected): void
	{
		$this->assertSame($expected, Arr::flatten($input));
	}

	/**
	 * Test that passing a non-array input throws TypeError.
	 */
	public function testFlattenThrowsTypeErrorOnInvalidInput(): void
	{
		$this->expectException(TypeError::class);

		/** @noinspection PhpParamsInspection intentionally passing null */
		Arr::flatten(null);
	}

	/**
	 * Data provider for flatten tests.
	 *
	 * @return array
	 */
	public static function flattenProvider(): array
	{
		return [
			'simple-nested-array' => [
				[['something'], 'other'],
				['something', 'other'],
			],
			'indexed-nested-array' => [
				['A', ['B', ['C']]],
				['A', 'B', 'C'],
			],
			'empty-array' => [
				[],
				[],
			],
			'mixed-keys-array' => [
				[
					['John', 1],
					[['Post 1'], ['Post 2']],
				],
				['John', 1, 'Post 1', 'Post 2'],
			],
		];
	}
}
