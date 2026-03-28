<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\HTTP;
use Modseven\HTTP\Header;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\HTTP
 */
class HttpTest extends TestCase
{
    private array $originalServer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalServer = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // wwwFormUrlencode
    // -------------------------------------------------------------------------

    /** @dataProvider providerWwwFormUrlencode */
    public function testWwwFormUrlencode(array $params, string $expected): void
    {
        $this->assertSame($expected, HTTP::wwwFormUrlencode($params));
    }

    public static function providerWwwFormUrlencode(): array
    {
        return [
            'empty'          => [[], ''],
            'single param'   => [['foo' => 'bar'], 'foo=bar'],
            'multiple params'=> [['a' => '1', 'b' => '2'], 'a=1&b=2'],
            'encoded value'  => [['q' => 'hello world'], 'q=hello%20world'],
            'special chars'  => [['x' => 'a+b=c&d'], 'x=a%2Bb%3Dc%26d'],
        ];
    }

    // -------------------------------------------------------------------------
    // requestHeaders
    // -------------------------------------------------------------------------

    public function testRequestHeadersReturnsHeader(): void
    {
        // Arrange: inject HTTP_* keys into $_SERVER
        unset($_SERVER['CONTENT_TYPE'], $_SERVER['CONTENT_LENGTH']);
        $_SERVER['HTTP_ACCEPT']     = 'text/html';
        $_SERVER['HTTP_X_FOO_BAR']  = 'baz';

        $headers = HTTP::requestHeaders();

        $this->assertInstanceOf(Header::class, $headers);
    }

    public function testRequestHeadersParsesHttpVars(): void
    {
        // HTTP_* keys are transformed: HTTP_ACCEPT → 'accept'
        $_SERVER['HTTP_ACCEPT'] = 'text/html';

        $headers = HTTP::requestHeaders();

        $this->assertTrue($headers->offsetExists('accept'));
        $this->assertSame('text/html', $headers->offsetGet('accept'));
    }
}
