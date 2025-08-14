<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\Arr::map
 */
final class ArrMapTest extends TestCase
{
	/**
	 * Provides test data for testMap
	 *
	 * @return array
	 */
	public static function providerMap(): array
	{
		return [
			// single level, simple
			['strip_tags', ['<p>foobar</p>'], null, ['foobar']],

			// multiple arrays
			['strip_tags', [['<p>foobar</p>'], ['<p>foobar</p>']], null, [['foobar'], ['foobar']]],

			// associative array
			['strip_tags', ['foo' => '<p>foobar</p>', 'bar' => '<p>foobar</p>'], null, ['foo' => 'foobar', 'bar' => 'foobar']],

			// keys filter
			['strip_tags', ['foo' => '<p>foobar</p>', 'bar' => '<p>foobar</p>'], ['foo'], ['foo' => 'foobar', 'bar' => '<p>foobar</p>']],

			// multiple callbacks
			[['strip_tags','trim'], ['foo' => '<p>foobar </p>', 'bar' => '<p>foobar</p>'], null, ['foo' => 'foobar', 'bar' => 'foobar']],

			// nested array
			['strip_tags', [['foo' => '<p>foobar</p>', 'bar' => '<p>foobar</p>']], ['foo'], [['foo' => 'foobar', 'bar' => '<p>foobar</p>']]],
		];
	}

	/**
	 * @test
	 * @dataProvider providerMap/**
	 * @param callable|array $method
	 * @param array $array
	 * @param array|null $keys
	 * @param array $expected
	 */
	public function testMap($method, array $array, $keys, array $expected): void

	{
		$this->assertSame(
			$expected,
//			Arr::map($array, $method, $keys)
			Arr::map($method, $array, $keys)
		);
	}
}
