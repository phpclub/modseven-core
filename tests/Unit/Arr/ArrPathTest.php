<?php
namespace Modseven\Tests\Unit\Arr;

use ArrayObject;
use Modseven\Tests\Support\TestCase;
use Modseven\Arr;

class ArrPathTest extends TestCase
{
	/**
	 * Tests path traversal for nested array access
	 *
	 * @dataProvider pathProvider
	 */
	public function testPath($expected, array $array, $path, $default = null, ?string $delimiter = null): void
	{
		$result = Arr::path($array, $path, $default, $delimiter);
		$this->assertSame($expected, $result);
	}

	/**
	 * Tests path with custom delimiter
	 */
	public function testPathWithCustomDelimiter(): void
	{
		$array = ['level1' => ['level2' => 'value']];
		$result = Arr::path($array, 'level1/level2', null, '/');
		$this->assertSame('value', $result);
	}

	/**
	 * Tests path traversal with array as path parameter
	 */
	public function testPathWithArrayPath(): void
	{
		$array = ['users' => [2 => ['name' => 'john']]];
		$result = Arr::path($array, ['users', 2, 'name']);
		$this->assertSame('john', $result);
	}

	/**
	 * Tests wildcard functionality for collecting all values at a level
	 */
	public function testPathWithWildcards(): void
	{
		$array = [
			'users' => [
				1 => ['name' => 'matt'],
				2 => ['name' => 'john'],
			]
		];
		$result = Arr::path($array, 'users.*.name');
		$this->assertSame(['matt', 'john'], $result);
	}

	/**
	 * Tests ArrayObject compatibility
	 */
	public function testPathWithArrayObject(): void
	{
		$array = ['object' => new ArrayObject(['iterator' => true])];
		$result = Arr::path($array, 'object.iterator');
		$this->assertTrue($result);
	}

	/**
	 * Data provider for path testing scenarios
	 */
	public static function pathProvider(): array
	{
		$array = [
			'foobar' => ['definition' => 'lost'],
			'ko7' => 'awesome',
			'users' => [
				1 => ['name' => 'matt'],
				2 => ['name' => 'john', 'interests' => ['hockey' => ['length' => 2], 'football' => []]],
				3 => 'frank',
			],
			'clean_users' => [
				1 => ['name' => 'matt'],
				2 => ['name' => 'john'],
			],
			'object' => new ArrayObject(['iterator' => true]),
		];

		return [
			// Direct key access
			[$array['foobar'], $array, 'foobar'],
			[$array['ko7'], $array, 'ko7'],

			// Nested path access
			[$array['foobar']['definition'], $array, 'foobar.definition'],
			[$array['users'][1]['name'], $array, 'users.1.name'],

			// Custom delimiter
			[$array['foobar']['definition'], $array, 'foobar/definition', null, '/'],

			// Default value returns
			[null, $array, 'foobar.alternatives', null],
			['nothing', $array, 'ko7.alternatives', 'nothing'],
			[['far', 'wide'], $array, 'cheese.origins', ['far', 'wide']],

			// Wildcard - return entire level
			[$array['users'], $array, 'users.*'],

			// ArrayObject access
			[$array['object']['iterator'], $array, 'object.iterator'],

			// Non-existent paths
			['default', $array, 'nonexistent.path', 'default'],
			[null, $array, 'users.999.name', null],

			// Path as array
			[$array['users'][2]['name'], $array, ['users', 2, 'name']],

			// Wildcard with name extraction (using clean dataset without mixed types)
			[['matt', 'john'], $array['clean_users'], '*.name'],
		];
	}
}