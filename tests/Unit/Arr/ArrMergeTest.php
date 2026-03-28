<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\Arr::merge
 */
class ArrMergeTest extends TestCase
{
    /**
     * @dataProvider mergeDataProvider
     * @param array $expected
     * @param array ...$arrays
     */
    public function testMerge(array $expected, array ...$arrays): void
    {
        $this->assertSame($expected, Arr::merge(...$arrays));
    }

    public static function mergeDataProvider(): array
    {
        return [
            'recursive-merge' => [
                ['person' => ['name' => 'John', 'age' => 25, 'city' => 'NY']],
                ['person' => ['name' => 'John', 'age' => 30]],
                ['person' => ['age' => 25, 'city' => 'NY']],
            ],
            'numeric-append' => [
                [1, 2, 3],
                [1, 2],
                [2, 3],
            ],
            'mixed-numeric-and-assoc' => [
                ['a' => 1, 0 => 2, 'b' => 3],
                ['a' => 1, 0 => 2],
                ['b' => 3, 0 => 2],
            ],
            'multiple-arrays' => [
                [1, 2, 3],
                [1],
                [2],
                [3],
            ],
            'nested-merge' => [
                ['x', 'y', 'z'],
                ['x', 'y', 'z'],
                ['x', 'y'],
            ],
            'empty-first-array' => [
                [1],
                [],
                [1],
            ],
        ];
    }
}