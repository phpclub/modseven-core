<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\Inflector;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Inflector methods that have no Config dependency.
 * singular(), plural(), uncountable() are excluded — they require Config::load('inflector').
 *
 * @covers \Modseven\Inflector::camelize
 * @covers \Modseven\Inflector::decamelize
 * @covers \Modseven\Inflector::underscore
 * @covers \Modseven\Inflector::humanize
 */
class InflectorTest extends TestCase
{
    // -------------------------------------------------------------------------
    // camelize
    // -------------------------------------------------------------------------

    /** @dataProvider providerCamelize */
    public function testCamelize(string $input, string $expected): void
    {
        $this->assertSame($expected, Inflector::camelize($input));
    }

    public static function providerCamelize(): array
    {
        return [
            ['foo bar',    'fooBar'],
            ['foo_bar',    'fooBar'],
            ['foo_bar_baz','fooBarBaz'],
            ['FOO BAR',    'fooBar'],
            ['already',    'already'],
            ['  spaced  ', 'spaced'],
        ];
    }

    // -------------------------------------------------------------------------
    // decamelize
    // -------------------------------------------------------------------------

    /** @dataProvider providerDecamelize */
    public function testDecamelize(string $input, string $sep, string $expected): void
    {
        $this->assertSame($expected, Inflector::decamelize($input, $sep));
    }

    public static function providerDecamelize(): array
    {
        return [
            ['fooBar',     ' ', 'foo bar'],
            ['fooBarBaz',  ' ', 'foo bar baz'],
            ['fooBar',     '_', 'foo_bar'],
            ['alreadylower',' ','alreadylower'],
            ['FooBar',     ' ', 'foo bar'],
        ];
    }

    // -------------------------------------------------------------------------
    // underscore
    // -------------------------------------------------------------------------

    /** @dataProvider providerUnderscore */
    public function testUnderscore(string $input, string $expected): void
    {
        $this->assertSame($expected, Inflector::underscore($input));
    }

    public static function providerUnderscore(): array
    {
        return [
            ['foo bar',       'foo_bar'],
            ['foo  bar',      'foo_bar'],   // \s+ collapses multiple spaces into one underscore
            ['foo bar baz',   'foo_bar_baz'],
            ['  foo bar  ',   'foo_bar'],
            ['nospaces',      'nospaces'],
        ];
    }

    // -------------------------------------------------------------------------
    // humanize
    // -------------------------------------------------------------------------

    /** @dataProvider providerHumanize */
    public function testHumanize(string $input, string $expected): void
    {
        $this->assertSame($expected, Inflector::humanize($input));
    }

    public static function providerHumanize(): array
    {
        return [
            ['foo_bar',     'foo bar'],
            ['foo-bar',     'foo bar'],
            ['foo_bar_baz', 'foo bar baz'],
            ['foo-bar-baz', 'foo bar baz'],
            ['  foo_bar  ', 'foo bar'],
            ['nounderscore','nounderscore'],
        ];
    }
}
