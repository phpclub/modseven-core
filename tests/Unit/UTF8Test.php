<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\UTF8;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\UTF8
 *
 * Tests that do not require Core::findFile (pure regex, or mbstring path when server_utf8=true).
 * Methods that fall back to Core::findFile are skipped as they need the cascade FS.
 */
class UTF8Test extends TestCase
{
    // -------------------------------------------------------------------------
    // isAscii
    // -------------------------------------------------------------------------

    /** @dataProvider providerIsAscii */
    public function testIsAscii(mixed $input, bool $expected): void
    {
        $this->assertSame($expected, UTF8::isAscii($input));
    }

    public static function providerIsAscii(): array
    {
        return [
            ['hello',         true],
            ['',              true],
            ['Héllo',         false],
            ['Привет',        false],
            [['a', 'b'],      true],
            [['a', 'é'],      false],
        ];
    }

    // -------------------------------------------------------------------------
    // stripAsciiCtrl
    // -------------------------------------------------------------------------

    public function testStripAsciiCtrlRemovesControlChars(): void
    {
        // ASCII control chars: 0x00-0x08, 0x0B, 0x0C, 0x0E-0x1F, 0x7F
        $input = "hello\x00\x07world\x1F";
        $this->assertSame('helloworld', UTF8::stripAsciiCtrl($input));
    }

    public function testStripAsciiCtrlKeepsNormalText(): void
    {
        $this->assertSame("hello\nworld", UTF8::stripAsciiCtrl("hello\nworld"));
    }

    // -------------------------------------------------------------------------
    // stripNonAscii
    // -------------------------------------------------------------------------

    public function testStripNonAscii(): void
    {
        // accented chars (multi-byte) are removed entirely, not replaced
        $this->assertSame('hllo wrld', UTF8::stripNonAscii('héllo wörld'));
    }

    public function testStripNonAsciiLeavesAsciiUntouched(): void
    {
        $this->assertSame('abc123', UTF8::stripNonAscii('abc123'));
    }

    // -------------------------------------------------------------------------
    // strlen (uses mb_strlen when server_utf8=true)
    // -------------------------------------------------------------------------

    public function testStrlenAscii(): void
    {
        $this->assertSame(5, UTF8::strlen('hello'));
    }

    public function testStrlenUtf8(): void
    {
        // 'Привет' is 6 Cyrillic characters
        $this->assertSame(6, UTF8::strlen('Привет'));
    }

    public function testStrlenEmpty(): void
    {
        $this->assertSame(0, UTF8::strlen(''));
    }

    // -------------------------------------------------------------------------
    // strpos (uses mb_strpos when server_utf8=true)
    // -------------------------------------------------------------------------

    public function testStrposFound(): void
    {
        // h=0 e=1 l=2 l=3 o=4 (space)=5 w=6 → 'world' starts at 6
        $this->assertSame(6, UTF8::strpos('hello world', 'world'));
    }

    public function testStrposNotFound(): void
    {
        $this->assertFalse(UTF8::strpos('hello', 'xyz'));
    }

    public function testStrposWithOffset(): void
    {
        // 'abc' at 0, 3, 6 — with offset=1 the first match is at 3
        $this->assertSame(3, UTF8::strpos('abcabcabc', 'abc', 1));
    }

    public function testStrposUtf8(): void
    {
        // 'Привет мир': 'мир' starts at character index 7
        $this->assertSame(7, UTF8::strpos('Привет мир', 'мир'));
    }

    // -------------------------------------------------------------------------
    // strrpos (uses mb_strrpos when server_utf8=true)
    // -------------------------------------------------------------------------

    public function testStrrposFindsLast(): void
    {
        $this->assertSame(6, UTF8::strrpos('abcabcabc', 'abc', 0));
    }

    // -------------------------------------------------------------------------
    // substr (uses mb_substr when server_utf8=true)
    // -------------------------------------------------------------------------

    public function testSubstrAscii(): void
    {
        $this->assertSame('world', UTF8::substr('hello world', 6));
    }

    public function testSubstrWithLength(): void
    {
        $this->assertSame('hello', UTF8::substr('hello world', 0, 5));
    }

    public function testSubstrUtf8(): void
    {
        // 'Привет' → chars 1-3 = 'рив'
        $this->assertSame('рив', UTF8::substr('Привет', 1, 3));
    }

    // -------------------------------------------------------------------------
    // strtolower (uses mb_strtolower when server_utf8=true)
    // -------------------------------------------------------------------------

    public function testStrtolowerAscii(): void
    {
        $this->assertSame('hello world', UTF8::strtolower('HELLO WORLD'));
    }

    public function testStrtolowerUtf8(): void
    {
        $this->assertSame('привет', UTF8::strtolower('ПРИВЕТ'));
    }

    // -------------------------------------------------------------------------
    // strtoupper (uses mb_strtoupper when server_utf8=true)
    // -------------------------------------------------------------------------

    public function testStrtoupperAscii(): void
    {
        $this->assertSame('HELLO WORLD', UTF8::strtoupper('hello world'));
    }

    public function testStrtoupperUtf8(): void
    {
        $this->assertSame('ПРИВЕТ', UTF8::strtoupper('привет'));
    }
}
