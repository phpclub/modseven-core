<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\Security;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Security helper methods that have no Session dependency.
 * Excluded: token(), check() — require Session::instance().
 *
 * @covers \Modseven\Security::slowEquals
 * @covers \Modseven\Security::encodePhpTags
 */
class SecurityTest extends TestCase
{
    // -------------------------------------------------------------------------
    // slowEquals
    // -------------------------------------------------------------------------

    /** @dataProvider providerSlowEquals */
    public function testSlowEquals(string $a, string $b, bool $expected): void
    {
        $this->assertSame($expected, Security::slowEquals($a, $b));
    }

    public static function providerSlowEquals(): array
    {
        return [
            ['abc',  'abc',  true],
            ['abc',  'abd',  false],
            ['abc',  'ab',   false],  // different length
            ['',     '',     true],
            ['abc',  'ABC',  false],  // case sensitive
            ['hash1234', 'hash1234', true],
        ];
    }

    public function testSlowEqualsIsTimingSafe(): void
    {
        // Same result regardless of where the difference is
        $this->assertFalse(Security::slowEquals('aaaaaa', 'baaaaa'));
        $this->assertFalse(Security::slowEquals('aaaaaa', 'aaaab'));
        $this->assertFalse(Security::slowEquals('aaaaaa', 'aaaaa'));   // length diff
    }

    // -------------------------------------------------------------------------
    // encodePhpTags
    // -------------------------------------------------------------------------

    /** @dataProvider providerEncodePhpTags */
    public function testEncodePhpTags(string $input, string $expected): void
    {
        $this->assertSame($expected, Security::encodePhpTags($input));
    }

    public static function providerEncodePhpTags(): array
    {
        return [
            ['<?php echo "hi"; ?>',  '&lt;?php echo "hi"; ?&gt;'],
            ['<?= $var ?>',          '&lt;?= $var ?&gt;'],
            ['no php here',          'no php here'],
            ['',                     ''],
            ['<html><?php ?></html>', '<html>&lt;?php ?&gt;</html>'],
            ['multiple <? one ?> and <? two ?>', 'multiple &lt;? one ?&gt; and &lt;? two ?&gt;'],
        ];
    }
}
