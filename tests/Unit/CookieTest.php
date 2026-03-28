<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\Cookie;
use Modseven\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Subclass that intercepts _setcookie() to avoid sending actual headers.
 */
class TestableCookie extends Cookie
{
    public static bool $setCookieCalled = false;
    public static array $lastSetCookieArgs = [];

    protected static function _setcookie(
        string $name, string $value, int $expire,
        string $path, string $domain, bool $secure, bool $httponly
    ): bool {
        static::$setCookieCalled = true;
        static::$lastSetCookieArgs = compact('name', 'value', 'expire', 'path', 'domain', 'secure', 'httponly');
        return true;
    }

    public static function reset(): void
    {
        static::$setCookieCalled = false;
        static::$lastSetCookieArgs = [];
    }
}

/**
 * @covers \Modseven\Cookie
 */
class CookieTest extends TestCase
{
    private string $originalSalt;
    private string $originalUserAgent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalSalt = isset(Cookie::$salt) ? Cookie::$salt : '';
        $this->originalUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        Cookie::$salt   = 'test-salt-value';
        Cookie::$domain = '';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit/TestAgent';

        TestableCookie::$salt   = 'test-salt-value';
        TestableCookie::$domain = '';
        TestableCookie::reset();
    }

    protected function tearDown(): void
    {
        Cookie::$salt = $this->originalSalt;
        $_SERVER['HTTP_USER_AGENT'] = $this->originalUserAgent;
        unset($_COOKIE['test_cookie'], $_COOKIE['missing_cookie']);
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // salt
    // -------------------------------------------------------------------------

    public function testSaltReturnsString(): void
    {
        $salt = Cookie::salt('my_cookie', 'my_value');
        $this->assertIsString($salt);
        $this->assertNotEmpty($salt);
    }

    public function testSaltDiffersForDifferentValues(): void
    {
        $salt1 = Cookie::salt('cookie', 'value1');
        $salt2 = Cookie::salt('cookie', 'value2');
        $this->assertNotSame($salt1, $salt2);
    }

    public function testSaltDiffersForDifferentNames(): void
    {
        $salt1 = Cookie::salt('cookie1', 'value');
        $salt2 = Cookie::salt('cookie2', 'value');
        $this->assertNotSame($salt1, $salt2);
    }

    public function testSaltThrowsWhenNotConfigured(): void
    {
        $prev = Cookie::$salt;
        Cookie::$salt = '';
        $this->expectException(Exception::class);
        try {
            Cookie::salt('name', 'value');
        } finally {
            Cookie::$salt = $prev;
        }
    }

    // -------------------------------------------------------------------------
    // get
    // -------------------------------------------------------------------------

    public function testGetReturnsMissingDefault(): void
    {
        unset($_COOKIE['missing_cookie']);
        $this->assertNull(Cookie::get('missing_cookie'));
        $this->assertSame('fallback', Cookie::get('missing_cookie', 'fallback'));
    }

    public function testGetReturnValueForValidSignedCookie(): void
    {
        $name  = 'test_cookie';
        $value = 'hello';
        $hash  = Cookie::salt($name, $value);
        $_COOKIE[$name] = $hash . '~' . $value;

        $this->assertSame($value, Cookie::get($name));
    }

    public function testGetReturnsDefaultForTamperedCookie(): void
    {
        $name = 'test_cookie';
        // Correct hash but wrong value
        $hash = Cookie::salt($name, 'original');
        $_COOKIE[$name] = $hash . '~tampered';

        $this->assertNull(Cookie::get($name));
    }

    public function testGetReturnsDefaultForMalformedCookie(): void
    {
        // No '~' separator at the expected position
        $_COOKIE['test_cookie'] = 'noseparatorhere';
        $this->assertNull(Cookie::get('test_cookie'));
    }

    // -------------------------------------------------------------------------
    // set (via TestableCookie to avoid header output)
    // -------------------------------------------------------------------------

    public function testSetCallsSetCookie(): void
    {
        TestableCookie::set('my_cookie', 'my_value', 0);

        $this->assertTrue(TestableCookie::$setCookieCalled);
        $this->assertSame('my_cookie', TestableCookie::$lastSetCookieArgs['name']);
        $this->assertStringContainsString('~my_value', TestableCookie::$lastSetCookieArgs['value']);
    }

    public function testSetValueIsSigned(): void
    {
        TestableCookie::set('signed_cookie', 'secret');

        $stored = TestableCookie::$lastSetCookieArgs['value'];
        // Format: hash~value
        $this->assertStringContainsString('~secret', $stored);
        $parts = explode('~', $stored, 2);
        $this->assertCount(2, $parts);
        $this->assertSame($parts[0], TestableCookie::salt('signed_cookie', 'secret'));
    }

    // -------------------------------------------------------------------------
    // delete (via TestableCookie)
    // -------------------------------------------------------------------------

    public function testDeleteUnsetsAndExpiresCookie(): void
    {
        $_COOKIE['del_cookie'] = 'some_value';

        TestableCookie::delete('del_cookie');

        $this->assertArrayNotHasKey('del_cookie', $_COOKIE);
        $this->assertTrue(TestableCookie::$setCookieCalled);
        $this->assertLessThan(time(), TestableCookie::$lastSetCookieArgs['expire']);
    }
}
