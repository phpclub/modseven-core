<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use Modseven\Tests\Support\TestCase;
use stdClass;

class ArrUnshiftTest extends TestCase
{
	/**
	 * @dataProvider unshiftProvider
	 * @param array $array Array to modify
	 * @param string $key Key to add
	 * @param mixed $value Value to add
	 * @param array $expected The expected array after modification
	 */
	public function testUnshift(array $array, string $key, mixed $value, array $expected): void
	{
		$result = Arr::unshift($array, $key, $value);

		// Assert that the array has the expected values and keys, ignoring type differences.
		$this->assertEquals($expected, $array);

		// Use assertSame to check that the returned array is the same instance.
		$this->assertSame($array, $result);
	}

	/**
	 * Test unshift preserves the order of existing keys.
	 */
	public function testUnshiftPreservesOrder(): void
	{
		$array = ['second' => '2nd', 'third' => '3rd'];
		Arr::unshift($array, 'first', '1st');

		$keys = array_keys($array);
		$this->assertSame(['first', 'second', 'third'], $keys);

		$values = array_values($array);
		$this->assertSame(['1st', '2nd', '3rd'], $values);
	}

	/**
	 * Test unshift correctly overwrites an existing key.
	 */
	public function testUnshiftOverwritesExistingKey(): void
	{
		$array = ['existing' => 'old_value', 'other' => 'other_value'];
		Arr::unshift($array, 'existing', 'new_value');

		// New value should be at the beginning
		$this->assertSame('new_value', reset($array));
		$this->assertSame('existing', key($array));

		// The array count should not change, as the key is overwritten, not duplicated
		$this->assertCount(2, $array);
	}

	/**
	 * Test unshift with various value types.
	 */
	public function testUnshiftWithVariousValueTypes(): void
	{
		$testCases = [
			['key_int', 123],
			['key_string', 'text'],
			['key_bool', true],
			['key_array', ['nested', 'array']],
			['key_null', null],
			['key_object', new stdClass()],
		];

		foreach ($testCases as [$key, $value]) {
			$array = ['existing' => 'value'];
			Arr::unshift($array, $key, $value);

			$this->assertSame($value, reset($array));
			$this->assertSame($key, key($array));
		}
	}

	/**
	 * @return array<int, array{array, string, mixed, array}>
	 */
	public static function unshiftProvider(): array
	{
		return [
			// Case 1: Unshifting into a non-empty associative array
			[
				['ko7' => 'awesome', 'blueflame' => 'was'],
				'zero',
				'0',
				['zero' => '0', 'ko7' => 'awesome', 'blueflame' => 'was']
			],
			// Case 2: Unshifting into an indexed array
			[
				['step 1', 'step 2', 'step 3'],
				'step 0',
				'wow',
				['step 0' => 'wow', 'step 1', 'step 2', 'step 3']
			],
			// Case 3: Unshifting a complex value
			[
				['name' => 'John', 'age' => 30],
				'id',
				123,
				['id' => 123, 'name' => 'John', 'age' => 30]
			],
			// Case 4: Unshifting into an empty array
			[
				[],
				'first_key',
				'first_value',
				['first_key' => 'first_value']
			],
			// Case 5: Unshifting with a numeric string key, which will be cast to an integer by PHP
			[
				['existing' => 'value'],
				'0',
				'zero',
				[0 => 'zero', 'existing' => 'value']
			],
			// Case 6: Unshifting with a non-numeric string key into an indexed array
			[
				['a', 'b', 'c'],
				'prefix',
				'prefixed',
				['prefix' => 'prefixed', 'a', 'b', 'c']
			]
		];
	}
}