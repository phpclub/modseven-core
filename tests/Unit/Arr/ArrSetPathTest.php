<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use Modseven\Tests\Support\TestCase;

class ArrSetPathTest extends TestCase
{
	/**
	 * @dataProvider setPathProvider
	 * @param array $expected Expected array state after setting the path
	 * @param array $array The array to modify
	 * @param array|string $path Path to set
	 * @param mixed $value Value to set
	 * @param string|null $delimiter Path delimiter
	 */
	public function testSetPath(array $expected, array $array, array|string $path, mixed $value, string $delimiter = null): void
	{
		Arr::setPath($array, $path, $value, $delimiter);

		$this->assertSame($expected, $array);
	}

	/**
	 * Test setting a path with a custom delimiter defined in the class.
	 */
	public function testSetPathWithStaticDelimiter(): void
	{
		$array = [];
		Arr::$delimiter = '-';

		Arr::setPath($array, 'ko7-is-awesome', 'value');
		$expected = [
			'ko7' => [
				'is' => [
					'awesome' => 'value'
				]
			]
		];

		$this->assertSame($expected, $array);

		// Reset delimiter to default
		Arr::$delimiter = '.';
	}

	/**
	 * Test that setting a numeric key works correctly.
	 */
	public function testSetPathWithNumericKey(): void
	{
		$array = ['users' => []];
		Arr::setPath($array, 'users.0.name', 'John');

		$expected = [
			'users' => [
				0 => [
					'name' => 'John'
				]
			]
		];

		$this->assertSame($expected, $array);
	}

	/**
	 * Test that an existing value is overwritten.
	 */
	public function testSetPathOverwritesExistingValue(): void
	{
		$array = ['name' => 'Old Name'];
		Arr::setPath($array, 'name', 'New Name');

		$expected = ['name' => 'New Name'];

		$this->assertSame($expected, $array);
	}

	/**
	 * @return array<int, array{array, array, array|string, mixed, string|null}>
	 */
	public static function setPathProvider(): array
	{
		return [
			// Create a simple nested array
			[['foo' => 'bar'], [], 'foo', 'bar'],

			// Create a deeper nested array
			[['ko7' => ['is' => 'awesome']], [], 'ko7.is', 'awesome'],

			// Modify an existing array
			[['ko7' => ['is' => 'cool', 'and' => 'slow']], ['ko7' => ['is' => 'cool']], 'ko7.and', 'slow'],

			// Use a custom delimiter
			[['ko7' => ['is' => 'awesome']], [], 'ko7/is', 'awesome', '/'],

			// Set value by an indexed path
			[['foo' => ['bar']], ['foo' => ['test']], 'foo.0', 'bar'],

			// Use a path as an array
			[['ko7' => ['is' => 'awesome']], [], ['ko7', 'is'], 'awesome'],

			// Overwrite a sub-array with a value
			[['ko7' => 'not an array anymore'], ['ko7' => ['is' => 'awesome']], 'ko7', 'not an array anymore'],
		];
	}
}