<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\I18n;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\I18n
 */
class I18nTest extends TestCase
{
    private string $originalLang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalLang = I18n::$lang;
    }

    protected function tearDown(): void
    {
        I18n::$lang = $this->originalLang;
        // Clear translation cache so next test gets a fresh load
        $ref = new \ReflectionProperty(I18n::class, '_cache');
        $ref->setAccessible(true);
        $ref->setValue(null, []);
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // lang getter/setter
    // -------------------------------------------------------------------------

    public function testLangDefaultIsEnUs(): void
    {
        I18n::$lang = 'en-us';
        $this->assertSame('en-us', I18n::lang());
    }

    public function testLangSetsNewValue(): void
    {
        I18n::lang('fr-fr');
        $this->assertSame('fr-fr', I18n::lang());
    }

    public function testLangNormalizesUnderscoreToDash(): void
    {
        I18n::lang('zh_CN');
        $this->assertSame('zh-cn', I18n::lang());
    }

    public function testLangNormalizesSpaceToDash(): void
    {
        I18n::lang('en US');
        $this->assertSame('en-us', I18n::lang());
    }

    public function testLangNullDoesNotChangeValue(): void
    {
        I18n::lang('de-de');
        I18n::lang(null);
        $this->assertSame('de-de', I18n::lang());
    }

    // -------------------------------------------------------------------------
    // get — no translation files in test env → returns original string
    // -------------------------------------------------------------------------

    public function testGetReturnsOriginalWhenNoTranslation(): void
    {
        $result = I18n::get('Hello, world');
        $this->assertSame('Hello, world', $result);
    }

    public function testGetWithValueSubstitution(): void
    {
        $result = I18n::get(['Hello, :name', [':name' => 'Alice']]);
        $this->assertSame('Hello, Alice', $result);
    }

    // -------------------------------------------------------------------------
    // __() global function
    // -------------------------------------------------------------------------

    public function testDoublUnderscoreReturnsString(): void
    {
        I18n::$lang = 'en-us';
        $this->assertSame('Test string', __('Test string'));
    }

    public function testDoubleUnderscoreWithValues(): void
    {
        I18n::$lang = 'en-us';
        $result = __('Hello :user', [':user' => 'Bob'], 'en-us');
        $this->assertSame('Hello Bob', $result);
    }
}
