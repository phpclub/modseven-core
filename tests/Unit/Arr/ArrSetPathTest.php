<?php declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;
use ArrayObject;

class ArrSetPathTest extends TestCase
{
	/**
	 * @dataProvider providerSetPath
	 * @param array        $initial   Initial array passed by reference.
	 * @param string|array $path      Path as string or array.
	 * @param mixed        $value     Value to set.
	 * @param array        $expected  Expected array after set.
	 * @param string|null  $delimiter Optional delimiter for path splitting.
	 */
	public function testSetPath(
		array &$initial,
			  $path,
			  $value,
		array $expected,
			  $delimiter = null
	): void {
		if ($delimiter !== null) {
			Arr::$delimiter = $delimiter;
		}

		Arr::setPath($initial, $path, $value);

		$this->assertSame($expected, $initial);

		if ($delimiter !== null) {
			Arr::$delimiter = '.';
		}
	}

	public static function providerSetPath(): array
	{
		return [
			'simple nested value' => [
				['foo' => 'bar'], 'foo', 'bar', ['foo' => 'bar'],
			],
			'create nested chain' => [
				[], 'a.b.c', 'value', ['a' => ['b' => ['c' => 'value']]],
			],
			'with custom delimiter' => [
				[], 'x/y/z', 'val', ['x' => ['y' => ['z' => 'val']]], '/',
			],
			'numeric key' => [
				['users' => []], 'users.0.name', 'John',
				['users' => [0 => ['name' => 'John']]],
			],
			'overwrite existing key' => [
				['name' => 'Old'], 'name', 'New',
				['name' => 'New'],
			],
			'overwrite array element' => [
				['ko7' => ['old' => 'val']], 'ko7.sub', 'v',
				['ko7' => ['old' => 'val', 'sub' => 'v']],
			],
			'path as array' => [
				['data' => []], ['data', 'item'], 'x',
				['data' => ['item' => 'x']],
			],
		];
	}
}
