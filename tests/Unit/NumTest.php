<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\Exception;
use Modseven\Num;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\Num
 */
class NumTest extends TestCase
{
    // -------------------------------------------------------------------------
    // ordinal
    // -------------------------------------------------------------------------

    /** @dataProvider providerOrdinal */
    public function testOrdinal(int $number, string $expected): void
    {
        $this->assertSame($expected, Num::ordinal($number));
    }

    public static function providerOrdinal(): array
    {
        return [
            [1,   'st'],
            [2,   'nd'],
            [3,   'rd'],
            [4,   'th'],
            [10,  'th'],
            [11,  'th'],  // teen exception
            [12,  'th'],  // teen exception
            [13,  'th'],  // teen exception
            [14,  'th'],
            [21,  'st'],
            [22,  'nd'],
            [23,  'rd'],
            [100, 'th'],
            [101, 'st'],
            [111, 'th'],  // teen exception in hundreds
            [112, 'th'],
            [113, 'th'],
            [121, 'st'],
        ];
    }

    // -------------------------------------------------------------------------
    // format
    // -------------------------------------------------------------------------

    /** @dataProvider providerFormat */
    public function testFormat(float $number, int $places, bool $monetary, string $expected): void
    {
        $this->assertSame($expected, Num::format($number, $places, $monetary));
    }

    public static function providerFormat(): array
    {
        // Thousands separator is locale-dependent (empty in C/POSIX locale used in CI).
        // Test only decimal formatting which is locale-stable.
        return [
            [0.0,   0, false, '0'],
            [-42.5, 1, false, '-42.5'],
            [1.0,   3, false, '1.000'],
            [9.99,  2, false, '9.99'],
        ];
    }

    // -------------------------------------------------------------------------
    // round
    // -------------------------------------------------------------------------

    /** @dataProvider providerRound */
    public function testRound(float $value, int $precision, int $mode, bool $native, ?float $expected): void
    {
        $this->assertSame($expected, Num::round($value, $precision, $mode, $native));
    }

    public static function providerRound(): array
    {
        return [
            // Native mode: mode is passed directly to PHP round()
            [2.5,  0, Num::ROUND_HALF_UP,   true,  3.0],
            [2.5,  0, Num::ROUND_HALF_DOWN, true,  2.0],  // PHP_ROUND_HALF_DOWN=2 → rounds toward zero
            [-2.5, 0, Num::ROUND_HALF_UP,   true, -3.0],

            // Userland ROUND_HALF_UP (falls through to round())
            [2.5,  0, Num::ROUND_HALF_UP,   false, 3.0],
            [3.5,  0, Num::ROUND_HALF_UP,   false, 4.0],

            // Userland ROUND_HALF_DOWN: tie → floor (round down)
            [2.5,  0, Num::ROUND_HALF_DOWN, false, 2.0],
            [3.5,  0, Num::ROUND_HALF_DOWN, false, 3.0],

            // Userland ROUND_HALF_EVEN: tie → ceil when floor(x) is truthy AND mode==EVEN
            // floor(2.5)=2, (bool)2=true, mode==EVEN=true → true===true → ceil → 3.0
            [2.5,  0, Num::ROUND_HALF_EVEN, false, 3.0],
            [3.5,  0, Num::ROUND_HALF_EVEN, false, 4.0],

            // Userland ROUND_HALF_ODD: tie → floor when floor(x) is truthy AND mode!=EVEN
            // floor(2.5)=2, (bool)2=true, mode==EVEN=false → true===false → floor → 2.0
            [2.5,  0, Num::ROUND_HALF_ODD,  false, 2.0],
            [3.5,  0, Num::ROUND_HALF_ODD,  false, 3.0],

            // Precision
            [1.555, 2, Num::ROUND_HALF_UP,  true, 1.56],
        ];
    }

    // -------------------------------------------------------------------------
    // bytes
    // -------------------------------------------------------------------------

    /** @dataProvider providerBytes */
    public function testBytes(string $size, float $expected): void
    {
        $this->assertSame($expected, Num::bytes($size));
    }

    public static function providerBytes(): array
    {
        return [
            ['256B',  256.0],
            ['1K',    1024.0],
            ['1KB',   1024.0],
            ['1KiB',  1024.0],
            ['1M',    1048576.0],
            ['1MB',   1048576.0],
            ['1.5MB', 1572864.0],
            ['1G',    1073741824.0],
            ['2GB',   2147483648.0],
            ['100',   100.0],   // no unit defaults to B
        ];
    }

    public function testBytesThrowsOnInvalidFormat(): void
    {
        $this->expectException(Exception::class);
        Num::bytes('abc');
    }
}
