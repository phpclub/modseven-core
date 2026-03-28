<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\URL;
use PHPUnit\Framework\TestCase;

/**
 * Tests for URL helper methods that have no Core/Request dependencies.
 * Excluded: base(), site() — require Core::$base_url and HTTP_HOST.
 *
 * @covers \Modseven\URL
 */
class UrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Isolate from any ambient $_GET values
        $_GET = [];
    }

    protected function tearDown(): void
    {
        $_GET = [];
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // isTrustedHost
    // -------------------------------------------------------------------------

    /** @dataProvider providerIsTrustedHost */
    public function testIsTrustedHost(string $host, array $trusted, bool $expected): void
    {
        $this->assertSame($expected, URL::isTrustedHost($host, $trusted));
    }

    public static function providerIsTrustedHost(): array
    {
        return [
            ['example.com',     ['example\.com'],       true],
            ['example.com',     ['other\.com'],          false],
            ['sub.example.com', ['.*\.example\.com'],    true],
            ['example.com',     ['.*\.example\.com'],    false],  // bare domain doesn't match subdomain pattern
            ['evil.com',        ['example\.com'],        false],
            ['localhost',       ['localhost'],            true],
            // empty $trusted_hosts falls back to Config::load() — tested separately
        ];
    }

    // -------------------------------------------------------------------------
    // query
    // -------------------------------------------------------------------------

    /** @dataProvider providerQuery */
    public function testQuery(?array $params, bool $useGet, string $expected): void
    {
        $this->assertSame($expected, URL::query($params, $useGet));
    }

    public static function providerQuery(): array
    {
        return [
            [['sort' => 'title', 'limit' => '10'], false, '?sort=title&limit=10'],
            [['q' => 'hello world'],                false, '?q=hello+world'],
            [null,                                  false, ''],
            [[],                                    false, ''],
            [['key' => null],                       false, ''],  // null values excluded by http_build_query
        ];
    }

    public function testQueryMergesWithGet(): void
    {
        $_GET = ['page' => '1'];
        $result = URL::query(['sort' => 'name'], true);
        $this->assertStringContainsString('page=1', $result);
        $this->assertStringContainsString('sort=name', $result);
    }

    public function testQueryUsesOnlyGetWhenNoParams(): void
    {
        $_GET = ['foo' => 'bar'];
        $result = URL::query(null, true);
        $this->assertSame('?foo=bar', $result);
    }

    // -------------------------------------------------------------------------
    // title
    // -------------------------------------------------------------------------

    /** @dataProvider providerTitle */
    public function testTitle(string $input, string $separator, bool $asciiOnly, string $expected): void
    {
        $this->assertSame($expected, URL::title($input, $separator, $asciiOnly));
    }

    public static function providerTitle(): array
    {
        return [
            ['My Blog Post',        '-', false, 'my-blog-post'],
            ['Hello   World',       '-', false, 'hello-world'],
            ['foo_bar baz',         '-', false, 'foobar-baz'],  // underscore is not \pL\pN\s or separator, gets stripped
            ['My Blog Post',        '_', false, 'my_blog_post'],
            ['-leading-trailing-',  '-', false, 'leading-trailing'],
            ['Héllo Wörld',         '-', true,  'hello-world'],
            ['Привет мир',          '-', true,  'privet-mir'],
            ['café',                '-', false, 'café'],
            ['café',                '-', true,  'cafe'],
        ];
    }
}
