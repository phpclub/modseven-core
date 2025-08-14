<?php

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;

class ArrMergeTest extends TestCase
{
	/**
	 * @dataProvider mergeDataProvider
	 */
	public function testMerge(...$args)
	{
		$expected = array_pop($args);
		$result = Arr::merge(...$args);
		$this->assertSame($expected, $result);
	}

	public static function mergeDataProvider(): array
	{
		return [
			'recursive-merge' => [
				['person' => ['name' => 'John', 'age' => 30]],
				['person' => ['age' => 25, 'city' => 'NY']],
				['person' => ['name' => 'John', 'age' => 25, 'city' => 'NY']],
			],
			'numeric-append' => [
				[1, 2],
				[2, 3],
				[1, 2, 3],
			],
			'mixed-numeric-and-assoc' => [
				['a' => 1, 0 => 2],
				['b' => 3, 0 => 2],
				['a' => 1, 0 => 2, 'b' => 3],
			],
			'multiple-arrays' => [
				[1],
				[2],
				[3],
				[1, 2, 3],
			],
			'nested-merge' => [
				['x', 'y', 'z'],
				['x', 'y'],
				['z'],
				['x', 'y', 'z'],
			],
			'empty-first-array' => [
				[],
				[1],
				[1],
			],
		];
	}
}
