<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\HTML;
use PHPUnit\Framework\TestCase;

/**
 * Tests for HTML helper methods.
 * Excluded: anchor/style/script/image with relative URIs (require URL::site → Core init).
 *
 * @covers \Modseven\HTML
 */
class HtmlTest extends TestCase
{
    private bool $originalStrict;
    private bool $originalWindowedUrls;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalStrict      = HTML::$strict;
        $this->originalWindowedUrls = HTML::$windowed_urls;
    }

    protected function tearDown(): void
    {
        HTML::$strict        = $this->originalStrict;
        HTML::$windowed_urls = $this->originalWindowedUrls;
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // entities
    // -------------------------------------------------------------------------

    /** @dataProvider providerEntities */
    public function testEntities(string $input, string $expected): void
    {
        $this->assertSame($expected, HTML::entities($input));
    }

    public static function providerEntities(): array
    {
        return [
            ['<script>alert(1)</script>', '&lt;script&gt;alert(1)&lt;/script&gt;'],
            ['"quoted"',                  '&quot;quoted&quot;'],
            ["it's",                      'it&#039;s'],
            ['plain text',                'plain text'],
            ['',                          ''],
        ];
    }

    public function testEntitiesNoDoubleEncode(): void
    {
        // With double_encode=false, existing entities are not re-encoded
        $this->assertSame('&amp;', HTML::entities('&amp;', false));
        $this->assertSame('&amp;amp;', HTML::entities('&amp;', true));
    }

    // -------------------------------------------------------------------------
    // chars
    // -------------------------------------------------------------------------

    /** @dataProvider providerChars */
    public function testChars(string $input, string $expected): void
    {
        $this->assertSame($expected, HTML::chars($input));
    }

    public static function providerChars(): array
    {
        return [
            ['<b>bold</b>', '&lt;b&gt;bold&lt;/b&gt;'],
            ['"quoted"',    '&quot;quoted&quot;'],
            ["it's",        'it&#039;s'],
            ['safe text',   'safe text'],
        ];
    }

    // -------------------------------------------------------------------------
    // attributes
    // -------------------------------------------------------------------------

    public function testAttributesEmpty(): void
    {
        $this->assertSame('', HTML::attributes(null));
        $this->assertSame('', HTML::attributes([]));
    }

    public function testAttributesSimple(): void
    {
        $result = HTML::attributes(['class' => 'foo', 'id' => 'bar']);
        $this->assertStringContainsString(' id="bar"', $result);
        $this->assertStringContainsString(' class="foo"', $result);
    }

    public function testAttributesOrder(): void
    {
        // 'id' appears before 'class' in $attribute_order
        $result = HTML::attributes(['class' => 'foo', 'id' => 'bar']);
        $this->assertLessThan(strpos($result, 'class'), strpos($result, 'id'));
    }

    public function testAttributesSkipsNull(): void
    {
        $result = HTML::attributes(['id' => 'bar', 'class' => null]);
        $this->assertStringContainsString('id="bar"', $result);
        $this->assertStringNotContainsString('class', $result);
    }

    public function testAttributesNumericKeyStrictMode(): void
    {
        // Numeric (mirrored) key in strict mode: disabled="disabled"
        HTML::$strict = true;
        $result = HTML::attributes(['disabled']);
        $this->assertStringContainsString('disabled="disabled"', $result);
    }

    public function testAttributesNumericKeyNonStrictMode(): void
    {
        // Non-strict: just the key, no value
        HTML::$strict = false;
        $result = HTML::attributes(['disabled']);
        $this->assertStringContainsString(' disabled', $result);
        $this->assertStringNotContainsString('="disabled"', $result);
    }

    // -------------------------------------------------------------------------
    // mailto
    // -------------------------------------------------------------------------

    public function testMailto(): void
    {
        $result = HTML::mailto('user@example.com');
        $this->assertStringStartsWith('<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;', $result);
        $this->assertStringContainsString('user@example.com', $result);
        $this->assertStringEndsWith('</a>', $result);
    }

    public function testMailtoWithTitle(): void
    {
        $result = HTML::mailto('user@example.com', 'Email me');
        $this->assertStringContainsString('>Email me</a>', $result);
    }

    public function testMailtoWithAttributes(): void
    {
        $result = HTML::mailto('user@example.com', null, ['class' => 'email-link']);
        $this->assertStringContainsString('class="email-link"', $result);
    }

    // -------------------------------------------------------------------------
    // anchor (only with absolute URLs — no URL::site() call needed)
    // -------------------------------------------------------------------------

    public function testAnchorAbsoluteUrl(): void
    {
        $result = HTML::anchor('https://example.com', 'Click');
        $this->assertSame('<a href="https://example.com">Click</a>', $result);
    }

    public function testAnchorUsesUriAsTitleWhenNone(): void
    {
        $result = HTML::anchor('https://example.com');
        $this->assertStringContainsString('>https://example.com</a>', $result);
    }

    public function testAnchorFragment(): void
    {
        // Fragment anchors (#) are not passed through URL::site()
        $result = HTML::anchor('#section', 'Jump');
        $this->assertSame('<a href="#section">Jump</a>', $result);
    }

    public function testAnchorQueryString(): void
    {
        // Query-string anchors (?) are not passed through URL::site()
        $result = HTML::anchor('?page=2', 'Next');
        $this->assertSame('<a href="?page=2">Next</a>', $result);
    }

    public function testAnchorWindowedUrls(): void
    {
        HTML::$windowed_urls = true;
        $result = HTML::anchor('https://external.com', 'External');
        $this->assertStringContainsString('target="_blank"', $result);
    }

    // -------------------------------------------------------------------------
    // style / script / image with absolute URLs
    // -------------------------------------------------------------------------

    public function testStyleAbsoluteUrl(): void
    {
        $result = HTML::style('https://example.com/style.css');
        $this->assertStringStartsWith('<link', $result);
        $this->assertStringContainsString('href="https://example.com/style.css"', $result);
        $this->assertStringContainsString('rel="stylesheet"', $result);
        $this->assertStringContainsString('type="text/css"', $result);
    }

    public function testScriptAbsoluteUrl(): void
    {
        $result = HTML::script('https://example.com/app.js');
        $this->assertStringStartsWith('<script', $result);
        $this->assertStringContainsString('src="https://example.com/app.js"', $result);
        $this->assertStringContainsString('type="text/javascript"', $result);
        $this->assertStringEndsWith('></script>', $result);
    }

    public function testImageAbsoluteUrl(): void
    {
        $result = HTML::image('https://example.com/photo.jpg', ['alt' => 'Photo']);
        $this->assertStringStartsWith('<img', $result);
        $this->assertStringContainsString('src="https://example.com/photo.jpg"', $result);
        $this->assertStringContainsString('alt="Photo"', $result);
    }

    public function testImageDataUri(): void
    {
        // data: URIs should not be passed through URL::site()
        $result = HTML::image('data:image/png;base64,abc123', ['alt' => 'icon']);
        $this->assertStringContainsString('src="data:image/png;base64,abc123"', $result);
    }
}
