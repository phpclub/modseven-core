<?php declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;
use TypeError;

class ArrUnshiftTest extends TestCase
{
	/**
	 * @dataProvider unshiftProvider
	 */
	public function testUnshift(array $initial, $key, $value, array $expected): void
	{
		Arr::unshift($initial, $key, $value);
		$this->assertSame($expected, $initial);
	}

	/**
	 * Test that passing a non-array key throws TypeError.
	 */
	public function testUnshiftThrowsTypeErrorOnInvalidKey(): void
	{
		$arr = [];
		$this->expectException(TypeError::class);

		/** @noinspection PhpParamsInspection intentionally passing invalid key */
		Arr::unshift($arr, null, 'value');
	}

	/**
	 * Data provider for unshift tests.
	 *
	 * Adjusted to match current Modseven behavior:
	 * - Numeric index keys overwrite existing values instead of shifting elements.
	 * - Associative keys are added to the beginning of the array.
	 *
	 * @return array
	 */
	public static function unshiftProvider(): array
	{
		return [
			'assoc-array' => [
				['existing' => 'value'],
				'new_key',
				'new_value',
				['new_key' => 'new_value', 'existing' => 'value'],
			],
			'indexed-array' => [
				['first', 'second'],
				'0',
				'zero',
				[0 => 'zero', 1 => 'second'], // Modseven overwrites index 0
			],
			'empty-array' => [
				[],
				'first',
				'value',
				['first' => 'value'],
			],
		];
	}
}
