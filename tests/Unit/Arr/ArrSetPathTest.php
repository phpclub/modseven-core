<?php declare(strict_types=1);

namespace Modseven\Tests\Unit\Arr;

use Modseven\Arr;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\Arr::setPath
 */
class ArrSetPathTest extends TestCase
{
    private string $originalDelimiter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDelimiter = Arr::$delimiter;
    }

    protected function tearDown(): void
    {
        Arr::$delimiter = $this->originalDelimiter;
        parent::tearDown();
    }

    /**
     * @dataProvider providerSetPath
     */
    public function testSetPath(
        array &$initial,
        string|array $path,
        mixed $value,
        array $expected,
        ?string $delimiter = null
    ): void {
        if ($delimiter !== null) {
            Arr::$delimiter = $delimiter;
        }

        Arr::setPath($initial, $path, $value);

        $this->assertSame($expected, $initial);
    }

    public static function providerSetPath(): array
    {
        return [
            'simple nested value' => [
                ['foo' => 'bar'], 'foo', 'bar', ['foo' => 'bar'],
            ],
            'create nested chain' => [
                [], 'a.b.c', 'value', ['a' => ['b' => ['c' => 'value']]],
            ],
            'with custom delimiter' => [
                [], 'x/y/z', 'val', ['x' => ['y' => ['z' => 'val']]], '/',
            ],
            'numeric key' => [
                ['users' => []], 'users.0.name', 'John',
                ['users' => [0 => ['name' => 'John']]],
            ],
            'overwrite existing key' => [
                ['name' => 'Old'], 'name', 'New',
                ['name' => 'New'],
            ],
            'overwrite array element' => [
                ['ko7' => ['old' => 'val']], 'ko7.sub', 'v',
                ['ko7' => ['old' => 'val', 'sub' => 'v']],
            ],
            'path as array' => [
                ['data' => []], ['data', 'item'], 'x',
                ['data' => ['item' => 'x']],
            ],
        ];
    }
}