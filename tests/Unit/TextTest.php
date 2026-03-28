<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\Text;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Text helper methods that have no external dependencies.
 * Excluded: userAgent() (requires Config), autoLink*() (requires HTML/Config).
 *
 * @covers \Modseven\Text
 */
class TextTest extends TestCase
{
    // -------------------------------------------------------------------------
    // limitWords
    // -------------------------------------------------------------------------

    /** @dataProvider providerLimitWords */
    public function testLimitWords(string $str, int $limit, ?string $endChar, string $expected): void
    {
        $this->assertSame($expected, Text::limitWords($str, $limit, $endChar));
    }

    public static function providerLimitWords(): array
    {
        return [
            ['one two three', 2, '…',  'one two…'],
            ['one two three', 3, '…',  'one two three'],
            ['one two three', 5, '…',  'one two three'],
            ['one two three', 2, '...',  'one two...'],
            ['',              2, '…',  ''],
            ['word',          1, '…',  'word'],
            ['one two',       0, '…',  '…'],
        ];
    }

    // -------------------------------------------------------------------------
    // limitChars
    // -------------------------------------------------------------------------

    /** @dataProvider providerLimitChars */
    public function testLimitChars(string $str, int $limit, ?string $endChar, bool $preserveWords, string $expected): void
    {
        $this->assertSame($expected, Text::limitChars($str, $limit, $endChar, $preserveWords));
    }

    public static function providerLimitChars(): array
    {
        return [
            ['hello world', 5,  '…', false, 'hello…'],
            ['hello world', 11, '…', false, 'hello world'],
            ['hello world', 5,  '…', true,  'hello…'],     // 'hello' fits, regex matches 'hello '
            ['hi there',   3,  '…', false, 'hi…'],        // substr(0,3)='hi ', rtrim='hi' then '…'
            ['',            5,  '…', false, ''],
            ['short',       10, '…', false, 'short'],
            ['hello',       0,  '…', false, '…'],
        ];
    }

    // -------------------------------------------------------------------------
    // alternate
    // -------------------------------------------------------------------------

    public function testAlternate(): void
    {
        // Reset the static counter
        Text::alternate();

        $this->assertSame('one',   Text::alternate('one', 'two', 'three'));
        $this->assertSame('two',   Text::alternate('one', 'two', 'three'));
        $this->assertSame('three', Text::alternate('one', 'two', 'three'));
        $this->assertSame('one',   Text::alternate('one', 'two', 'three'));
    }

    public function testAlternateResetWithNoArgs(): void
    {
        Text::alternate('a', 'b');
        Text::alternate('a', 'b');
        Text::alternate(); // reset

        $this->assertSame('a', Text::alternate('a', 'b'));
    }

    // -------------------------------------------------------------------------
    // random
    // -------------------------------------------------------------------------

    /** @dataProvider providerRandom */
    public function testRandomLength(string $type, int $length): void
    {
        $result = Text::random($type, $length);
        $this->assertSame($length, strlen($result));
    }

    public static function providerRandom(): array
    {
        return [
            ['alnum',    8],
            ['alpha',    6],
            ['hexdec',   16],
            ['numeric',  4],
            ['nozero',   4],
            ['distinct', 10],
        ];
    }

    public function testRandomAlnumContainsLetterAndDigit(): void
    {
        // alnum strings of length > 1 must contain at least one letter and one digit
        for ($i = 0; $i < 20; $i++) {
            $result = Text::random('alnum', 8);
            $this->assertMatchesRegularExpression('/[a-zA-Z]/', $result);
            $this->assertMatchesRegularExpression('/[0-9]/', $result);
        }
    }

    public function testRandomCustomPool(): void
    {
        $result = Text::random('abc', 20);
        $this->assertSame(20, strlen($result));
        $this->assertMatchesRegularExpression('/^[abc]+$/', $result);
    }

    // -------------------------------------------------------------------------
    // reduceSlashes
    // -------------------------------------------------------------------------

    /** @dataProvider providerReduceSlashes */
    public function testReduceSlashes(string $input, string $expected): void
    {
        $this->assertSame($expected, Text::reduceSlashes($input));
    }

    public static function providerReduceSlashes(): array
    {
        return [
            ['foo//bar',         'foo/bar'],
            ['foo///bar',        'foo/bar'],
            ['foo/bar',          'foo/bar'],
            ['http://example.com','http://example.com'],  // protocol double slash preserved
            ['a//b//c',          'a/b/c'],
        ];
    }

    // -------------------------------------------------------------------------
    // censor
    // -------------------------------------------------------------------------

    /** @dataProvider providerCensor */
    public function testCensor(string $str, array $words, string $replacement, bool $partial, string $expected): void
    {
        $this->assertSame($expected, Text::censor($str, $words, $replacement, $partial));
    }

    public static function providerCensor(): array
    {
        return [
            ['hello world',      ['world'],   '#',   true,  'hello #####'],
            ['hello world',      ['world'],   '[X]', true,  'hello [X]'],
            ['foo bar foo',      ['foo'],     '*',   true,  '*** bar ***'],
            ['nothing matches',  ['xyz'],     '#',   true,  'nothing matches'],
            ['test word here',   ['word'],    '#',   false, 'test #### here'],
            ['badword inside',   ['badword'], '#',   false, '####### inside'],
        ];
    }

    // -------------------------------------------------------------------------
    // similar
    // -------------------------------------------------------------------------

    /** @dataProvider providerSimilar */
    public function testSimilar(array $words, string $expected): void
    {
        $this->assertSame($expected, Text::similar($words));
    }

    public static function providerSimilar(): array
    {
        return [
            [['foo', 'foobar', 'foobaz'], 'foo'],
            [['hello', 'help', 'hell'],   'hel'],
            [['abc', 'abc', 'abc'],       'abc'],
            [['different', 'words'],      ''],
            [['a'],                       'a'],
        ];
    }

    // -------------------------------------------------------------------------
    // bytes (Text version — human-readable output)
    // -------------------------------------------------------------------------

    /** @dataProvider providerTextBytes */
    public function testBytes(int $bytes, ?string $forceUnit, bool $si, string $expected): void
    {
        $this->assertSame($expected, Text::bytes($bytes, $forceUnit, null, $si));
    }

    public static function providerTextBytes(): array
    {
        return [
            [0,       null,  true,  '0.00 B'],
            [1000,    null,  true,  '1.00 kB'],
            [1024,    null,  false, '1.00 KiB'],
            [1048576, null,  true,  '1.05 MB'],
            [1048576, null,  false, '1.00 MiB'],
            [512,     'B',   true,  '512.00 B'],
            [2097152, 'MiB', false, '2.00 MiB'],
        ];
    }

    // -------------------------------------------------------------------------
    // number (text representation)
    // -------------------------------------------------------------------------

    /** @dataProvider providerNumber */
    public function testNumber(int $number, string $expected): void
    {
        $this->assertSame($expected, Text::number($number));
    }

    public static function providerNumber(): array
    {
        return [
            [1,         'one'],
            [2,         'two'],
            [11,        'eleven'],
            [20,        'twenty'],
            [21,        'twenty-one'],
            [100,       'one hundred'],
            [101,       'one hundred and one'],
            [1000,      'one thousand'],
            [1001,      'one thousand and one'],
            [1000000,   'one million'],
        ];
    }

    // -------------------------------------------------------------------------
    // widont
    // -------------------------------------------------------------------------

    public function testWidont(): void
    {
        $result = Text::widont('<p>one two three</p>');
        $this->assertStringContainsString('&nbsp;', $result);
        // Last two words joined with &nbsp;
        $this->assertStringContainsString('two&nbsp;three', $result);
    }

    public function testWidontSingleWord(): void
    {
        $result = Text::widont('<p>word</p>');
        $this->assertStringNotContainsString('&nbsp;', $result);
    }

    // -------------------------------------------------------------------------
    // autoP
    // -------------------------------------------------------------------------

    /** @dataProvider providerAutoP */
    public function testAutoP(string $input, bool $br, string $expected): void
    {
        $this->assertSame($expected, Text::autoP($input, $br));
    }

    public static function providerAutoP(): array
    {
        return [
            ['',            true,  ''],
            ['hello',       true,  '<p>hello</p>'],
            ["line1\nline2",true,  "<p>line1<br />\nline2</p>"],
            ["line1\nline2",false, "<p>line1\nline2</p>"],
            ["a\n\nb",      true,  "<p>a</p>\n\n<p>b</p>"],
        ];
    }

    // -------------------------------------------------------------------------
    // ucfirst
    // -------------------------------------------------------------------------

    /** @dataProvider providerUcfirst */
    public function testUcfirst(string $input, string $delimiter, string $expected): void
    {
        $this->assertSame($expected, Text::ucfirst($input, $delimiter));
    }

    public static function providerUcfirst(): array
    {
        return [
            ['hello-world',   '-', 'Hello-World'],
            ['foo-bar-baz',   '-', 'Foo-Bar-Baz'],
            ['hello',         '-', 'Hello'],
        ];
    }
}
