<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use ArrayObject;
use Modseven\Valid;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\Valid
 */
class ValidTest extends TestCase
{
    // -------------------------------------------------------------------------
    // regex
    // -------------------------------------------------------------------------

    /** @dataProvider providerRegex */
    public function testRegex(?string $value, string $expression, bool $expected): void
    {
        $this->assertSame($expected, Valid::regex($value, $expression));
    }

    public static function providerRegex(): array
    {
        return [
            ['hello', '/^[a-z]+$/', true],
            ['Hello', '/^[a-z]+$/', false],
            ['123',   '/^\d+$/',    true],
            ['12a',   '/^\d+$/',    false],
            ['',      '/^$/',       true],
        ];
    }

    // -------------------------------------------------------------------------
    // minLength / maxLength / exactLength
    // -------------------------------------------------------------------------

    /** @dataProvider providerMinLength */
    public function testMinLength(?string $value, int $length, bool $expected): void
    {
        $this->assertSame($expected, Valid::minLength($value, $length));
    }

    public static function providerMinLength(): array
    {
        return [
            ['hello', 5, true],
            ['hello', 6, false],
            ['hi',    1, true],
            [null,    0, true],
            [null,    1, false],
        ];
    }

    /** @dataProvider providerMaxLength */
    public function testMaxLength(?string $value, int $length, bool $expected): void
    {
        $this->assertSame($expected, Valid::maxLength($value, $length));
    }

    public static function providerMaxLength(): array
    {
        return [
            ['hello', 5,  true],
            ['hello', 4,  false],
            ['hi',    10, true],
            [null,    0,  true],
        ];
    }

    /** @dataProvider providerExactLength */
    public function testExactLength(?string $value, int|array $length, bool $expected): void
    {
        $this->assertSame($expected, Valid::exactLength($value, $length));
    }

    public static function providerExactLength(): array
    {
        return [
            ['hello', 5,       true],
            ['hello', 4,       false],
            ['hi',    [2, 3],  true],
            ['hi',    [3, 4],  false],
            [null,    0,       true],
        ];
    }

    // -------------------------------------------------------------------------
    // equals
    // -------------------------------------------------------------------------

    /** @dataProvider providerEquals */
    public function testEquals(?string $value, string $required, bool $expected): void
    {
        $this->assertSame($expected, Valid::equals($value, $required));
    }

    public static function providerEquals(): array
    {
        return [
            ['foo',  'foo',  true],
            ['foo',  'bar',  false],
            ['',     '',     true],
            [null,   '',     false],
        ];
    }

    // -------------------------------------------------------------------------
    // email
    // -------------------------------------------------------------------------

    /** @dataProvider providerEmail */
    public function testEmail(?string $email, bool $expected): void
    {
        $this->assertSame($expected, Valid::email($email));
    }

    public static function providerEmail(): array
    {
        return [
            ['user@example.com',       true],
            ['user+tag@sub.domain.io', true],
            ['invalid-email',          false],
            ['@nodomain.com',          false],
            ['missing@',               false],
            [null,                     false],
        ];
    }

    // -------------------------------------------------------------------------
    // notEmpty
    // -------------------------------------------------------------------------

    /** @dataProvider providerNotEmpty */
    public function testNotEmpty(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, Valid::notEmpty($value));
    }

    public static function providerNotEmpty(): array
    {
        return [
            ['hello',           true],
            ['0',               true],
            [0,                 true],
            [['a'],             true],
            [null,              false],
            [false,             false],
            ['',                false],
            [[],                false],
            [new ArrayObject(), false],
            [new ArrayObject(['x' => 1]), true],
        ];
    }

    // -------------------------------------------------------------------------
    // url
    // -------------------------------------------------------------------------

    /** @dataProvider providerUrl */
    public function testUrl(?string $url, bool $expected): void
    {
        $this->assertSame($expected, Valid::url($url));
    }

    public static function providerUrl(): array
    {
        return [
            ['http://example.com',           true],
            ['https://sub.domain.org/path',  true],
            ['ftp://files.example.net',      true],
            ['http://192.168.1.1',           true],
            ['http://192.168.1.1:8080/path', true],
            ['not-a-url',                    false],
            ['http://',                      false],
        ];
    }

    // -------------------------------------------------------------------------
    // ip
    // -------------------------------------------------------------------------

    /** @dataProvider providerIp */
    public function testIp(?string $ip, bool $allowPrivate, bool $expected): void
    {
        $this->assertSame($expected, Valid::ip($ip, $allowPrivate));
    }

    public static function providerIp(): array
    {
        return [
            ['8.8.8.8',     true,  true],
            ['8.8.8.8',     false, true],
            ['192.168.1.1', true,  true],
            ['192.168.1.1', false, false],  // private range blocked
            ['::1',         true,  false],  // loopback is reserved
            ['999.999.999.999', true, false],
            [null,          true,  false],
        ];
    }

    // -------------------------------------------------------------------------
    // luhn
    // -------------------------------------------------------------------------

    /** @dataProvider providerLuhn */
    public function testLuhn(?string $number, bool $expected): void
    {
        $this->assertSame($expected, Valid::luhn($number));
    }

    public static function providerLuhn(): array
    {
        return [
            ['4532015112830366', true],   // valid Visa test number
            ['4532015112830367', false],  // off by one
            ['79927398713',      true],   // canonical Luhn example
            ['79927398710',      false],
            ['abc',              false],  // non-digit string
        ];
    }

    // -------------------------------------------------------------------------
    // phone
    // -------------------------------------------------------------------------

    /** @dataProvider providerPhone */
    public function testPhone(?string $number, ?array $lengths, bool $expected): void
    {
        $this->assertSame($expected, Valid::phone($number, $lengths));
    }

    public static function providerPhone(): array
    {
        return [
            ['555-1234',        null,     true],   // 7 digits after strip
            ['555-867-5309',    null,     true],   // 10 digits
            ['+1-800-555-0199', null,     true],   // 11 digits
            ['12',              null,     false],
            ['555-1234',        [7],      true],
            ['555-1234',        [10, 11], false],
        ];
    }

    // -------------------------------------------------------------------------
    // date
    // -------------------------------------------------------------------------

    /** @dataProvider providerDate */
    public function testDate(?string $str, bool $expected): void
    {
        $this->assertSame($expected, Valid::date($str));
    }

    public static function providerDate(): array
    {
        return [
            ['2024-01-15',   true],
            ['January 2024', true],
            ['tomorrow',     true],
            ['not a date',   false],
        ];
    }

    // -------------------------------------------------------------------------
    // alpha / alphaNumeric / alphaDash
    // -------------------------------------------------------------------------

    /** @dataProvider providerAlpha */
    public function testAlpha(?string $str, bool $utf8, bool $expected): void
    {
        $this->assertSame($expected, Valid::alpha($str, $utf8));
    }

    public static function providerAlpha(): array
    {
        return [
            ['hello',  false, true],
            ['Hello',  false, true],
            ['hello1', false, false],
            ['héllo',  true,  true],
            ['héllo',  false, false],
        ];
    }

    /** @dataProvider providerAlphaNumeric */
    public function testAlphaNumeric(?string $str, bool $utf8, bool $expected): void
    {
        $this->assertSame($expected, Valid::alphaNumeric($str, $utf8));
    }

    public static function providerAlphaNumeric(): array
    {
        return [
            ['hello1', false, true],
            ['Hello9', false, true],
            ['hi!',    false, false],
            ['café2',  true,  true],
        ];
    }

    /** @dataProvider providerAlphaDash */
    public function testAlphaDash(?string $str, bool $utf8, bool $expected): void
    {
        $this->assertSame($expected, Valid::alphaDash($str, $utf8));
    }

    public static function providerAlphaDash(): array
    {
        return [
            ['hello-world',  false, true],
            ['hello_world',  false, true],
            ['hello world',  false, false],
            ['slug-123',     false, true],
            ['café-du-monde',true,  true],
        ];
    }

    // -------------------------------------------------------------------------
    // digit
    // -------------------------------------------------------------------------

    /** @dataProvider providerDigit */
    public function testDigit(?string $str, bool $utf8, bool $expected): void
    {
        $this->assertSame($expected, Valid::digit($str, $utf8));
    }

    public static function providerDigit(): array
    {
        return [
            ['123',  false, true],
            ['12a',  false, false],
            ['-1',   false, false],
            ['٣',    true,  true],   // Arabic-Indic digit 3 (Unicode)
        ];
    }

    // -------------------------------------------------------------------------
    // numeric
    // -------------------------------------------------------------------------

    /** @dataProvider providerNumeric */
    public function testNumeric(?string $str, bool $expected): void
    {
        $this->assertSame($expected, Valid::numeric($str));
    }

    public static function providerNumeric(): array
    {
        return [
            ['42',   true],
            ['-42',  true],
            ['3.14', true],
            ['-0.5', true],
            ['1e5',  false],
            ['abc',  false],
        ];
    }

    // -------------------------------------------------------------------------
    // range
    // -------------------------------------------------------------------------

    /** @dataProvider providerRange */
    public function testRange(?string $number, int $min, int $max, ?int $step, bool $expected): void
    {
        $this->assertSame($expected, Valid::range($number, $min, $max, $step));
    }

    public static function providerRange(): array
    {
        return [
            ['5',  1, 10, null, true],
            ['0',  1, 10, null, false],
            ['11', 1, 10, null, false],
            ['6',  0, 10, 2,   true],   // step=2 from 0: 0,2,4,6,8,10 — 6 is on step
            ['5',  0, 10, 2,   false],  // 5 is not on step of 2
            ['4',  0, 10, 3,   false],  // step=3 from 0: 0,3,6,9 — 4 is not on step
            ['3',  0, 10, 3,   true],   // step=3 from 0: 3 is on step
        ];
    }

    // -------------------------------------------------------------------------
    // decimal
    // -------------------------------------------------------------------------

    /** @dataProvider providerDecimal */
    public function testDecimal(?string $str, int $places, ?int $digits, bool $expected): void
    {
        $this->assertSame($expected, Valid::decimal($str, $places, $digits));
    }

    public static function providerDecimal(): array
    {
        return [
            ['1.50',   2, null, true],
            ['1.5',    2, null, false],  // only 1 decimal place
            ['-1.50',  2, null, true],
            ['10.99',  2, 2,   true],
            ['100.99', 2, 2,   false],  // 3 digits, expected 2
        ];
    }

    // -------------------------------------------------------------------------
    // color
    // -------------------------------------------------------------------------

    /** @dataProvider providerColor */
    public function testColor(?string $str, bool $expected): void
    {
        $this->assertSame($expected, Valid::color($str));
    }

    public static function providerColor(): array
    {
        return [
            ['#fff',    true],
            ['#ffffff', true],
            ['fff',     true],
            ['ffffff',  true],
            ['#FFFFFF', true],
            ['#gg0000', false],
            ['#12345',  false],
        ];
    }

    // -------------------------------------------------------------------------
    // matches
    // -------------------------------------------------------------------------

    public function testMatches(): void
    {
        $data = ['password' => 'secret', 'confirm' => 'secret'];
        $this->assertTrue(Valid::matches($data, 'password', 'confirm'));

        $data['confirm'] = 'different';
        $this->assertFalse(Valid::matches($data, 'password', 'confirm'));
    }
}