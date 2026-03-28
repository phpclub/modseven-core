<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\Exception;
use Modseven\Request;
use Modseven\Route;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\Route
 */
class RouteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Isolate static route registry between tests
        $ref = new \ReflectionProperty(Route::class, '_routes');
        $ref->setAccessible(true);
        $ref->setValue(null, []);
    }

    // -------------------------------------------------------------------------
    // compile
    // -------------------------------------------------------------------------

    /** @dataProvider providerCompile */
    public function testCompileMatchesUri(string $pattern, ?array $regex, string $uri, bool $shouldMatch): void
    {
        $compiled = Route::compile($pattern, $regex);
        $result = (bool)preg_match($compiled, $uri);
        $this->assertSame($shouldMatch, $result);
    }

    public static function providerCompile(): array
    {
        return [
            // Simple static segment
            ['welcome/index',           null, 'welcome/index',    true],
            ['welcome/index',           null, 'welcome/other',    false],

            // Single key
            ['<controller>',            null, 'users',             true],
            ['<controller>',            null, 'users/edit',        false],

            // Two keys
            ['<controller>/<action>',   null, 'users/edit',        true],
            ['<controller>/<action>',   null, 'users',             false],

            // Optional group
            ['<controller>(/<action>)', null, 'users',             true],
            ['<controller>(/<action>)', null, 'users/edit',        true],

            // Custom regex for key
            ['<id>',                    ['id' => '[0-9]+'], '42',  true],
            ['<id>',                    ['id' => '[0-9]+'], 'abc', false],
        ];
    }

    // -------------------------------------------------------------------------
    // set / get / all / name
    // -------------------------------------------------------------------------

    public function testSetAndGet(): void
    {
        $route = Route::set('default', '<controller>(/<action>)');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($route, Route::get('default'));
    }

    public function testGetThrowsForUnknownRoute(): void
    {
        $this->expectException(Exception::class);
        Route::get('nonexistent');
    }

    public function testAll(): void
    {
        $this->assertSame([], Route::all());
        Route::set('first', 'foo');
        Route::set('second', 'bar');
        $this->assertCount(2, Route::all());
        $this->assertArrayHasKey('first', Route::all());
        $this->assertArrayHasKey('second', Route::all());
    }

    public function testName(): void
    {
        $route = Route::set('myroute', 'foo/bar');
        $this->assertSame('myroute', Route::name($route));
    }

    public function testNameReturnsEmptyStringForUnregistered(): void
    {
        // Route::name() has return type string, so array_search's false is coerced to ''
        $route = new Route('foo');
        $this->assertSame('', Route::name($route));
    }

    // -------------------------------------------------------------------------
    // defaults
    // -------------------------------------------------------------------------

    public function testDefaultsGetterReturnsCurrent(): void
    {
        $route = new Route('<controller>/<action>');
        $defaults = $route->defaults();
        $this->assertIsArray($defaults);
        $this->assertSame('index', $defaults['action']);
    }

    public function testDefaultsSetter(): void
    {
        $route = new Route('<controller>/<action>');
        $return = $route->defaults(['action' => 'list', 'controller' => 'posts']);
        $this->assertSame($route, $return);  // fluent
        $this->assertSame('list', $route->defaults()['action']);
    }

    // -------------------------------------------------------------------------
    // isExternal
    // -------------------------------------------------------------------------

    public function testIsExternalFalseByDefault(): void
    {
        $route = new Route('<controller>');
        $this->assertFalse($route->isExternal());
    }

    public function testIsExternalTrueWhenHostSet(): void
    {
        $route = new Route('<controller>');
        $route->defaults(['action' => 'index', 'host' => 'example.com']);
        $this->assertTrue($route->isExternal());
    }

    // -------------------------------------------------------------------------
    // uri
    // -------------------------------------------------------------------------

    public function testUriWithParams(): void
    {
        $route = new Route('<controller>/<action>');
        $this->assertSame('users/edit', $route->uri(['controller' => 'users', 'action' => 'edit']));
    }

    public function testUriUsesDefaultAction(): void
    {
        $route = new Route('<controller>(/<action>)');
        $route->defaults(['action' => 'index', 'host' => false]);
        $this->assertSame('users', $route->uri(['controller' => 'users']));
    }

    public function testUriThrowsWhenRequiredParamMissing(): void
    {
        // <id> has no default — passing only controller must throw
        $route = new Route('<controller>/<id>');
        $route->defaults(['action' => 'index', 'host' => false]);
        $this->expectException(\Modseven\Exception::class);
        $route->uri(['controller' => 'users']);
    }

    public function testUriForExternalRoute(): void
    {
        $route = new Route('<controller>');
        $route->defaults(['action' => 'index', 'host' => 'example.com']);
        $uri = $route->uri(['controller' => 'api']);
        $this->assertStringStartsWith('http://', $uri);
        $this->assertStringContainsString('example.com', $uri);
    }

    // -------------------------------------------------------------------------
    // filter
    // -------------------------------------------------------------------------

    public function testFilterAcceptsCallable(): void
    {
        $route = new Route('<controller>');
        $return = $route->filter(static fn($r, $p, $req) => $p);
        $this->assertSame($route, $return);  // fluent
    }

    public function testFilterThrowsForNonCallable(): void
    {
        $route = new Route('<controller>');
        $this->expectException(Exception::class);
        /** @noinspection PhpParamsInspection */
        $route->filter('not_a_real_callable_xyz_abc');
    }

    // -------------------------------------------------------------------------
    // matches
    // -------------------------------------------------------------------------

    private function stubRequest(string $uri): Request
    {
        // createMock cannot stub uri() due to its getter/setter signature.
        // Use a real Request instance — its constructor stores the URI directly.
        return new Request($uri);
    }

    public function testMatchesReturnsParamsOnSuccess(): void
    {
        $route = new Route('<controller>/<action>');
        $route->defaults(['action' => 'index', 'host' => false]);

        $params = $route->matches($this->stubRequest('users/edit'));
        $this->assertIsArray($params);
        $this->assertSame('users', $params['controller']);
        $this->assertSame('edit', $params['action']);
    }

    public function testMatchesReturnsFalseOnNoMatch(): void
    {
        $route = new Route('<controller>/<action>');
        $this->assertFalse($route->matches($this->stubRequest('')));
    }

    public function testMatchesFillsDefaultsForOptionalKeys(): void
    {
        $route = new Route('<controller>(/<action>)');
        $route->defaults(['action' => 'index', 'host' => false]);

        $params = $route->matches($this->stubRequest('users'));
        $this->assertSame('index', $params['action']);
    }

    public function testMatchesWithFilter(): void
    {
        $route = new Route('<controller>');
        $route->defaults(['action' => 'index', 'host' => false]);
        $route->filter(static function ($r, $params, $req) {
            $params['extra'] = 'injected';
            return $params;
        });

        $params = $route->matches($this->stubRequest('users'));
        $this->assertSame('injected', $params['extra']);
    }

    public function testMatchesFilterCanAbort(): void
    {
        $route = new Route('<controller>');
        $route->defaults(['action' => 'index', 'host' => false]);
        $route->filter(static fn() => false);

        $this->assertFalse($route->matches($this->stubRequest('users')));
    }
}
