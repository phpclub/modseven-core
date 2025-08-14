<?php
namespace Modseven\Tests\Unit\Arr;

use ArrayObject;
use Modseven\Tests\Support\TestCase;
use Modseven\Arr;

class ArrPathTest extends TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function testPath($expected, array $array, $path, $default = null, ?string $delimiter = null): void
    {
        $result = Arr::path($array, $path, $default, $delimiter);
        $this->assertSame($expected, $result);
    }

    public function testPathWithCustomDelimiter(): void
    {
        $array = ['level1' => ['level2' => 'value']];
        $result = Arr::path($array, 'level1/level2', null, '/');
        $this->assertSame('value', $result);
    }

    public function testPathWithArrayPath(): void
    {
        $array = ['users' => [2 => ['name' => 'john']]];
        $result = Arr::path($array, ['users', 2, 'name']);
        $this->assertSame('john', $result);
    }

    public function testPathWithWildcards(): void
    {
        $array = [
            'users' => [
                ['name' => 'matt'],
                ['name' => 'john'],
            ]
        ];
        $result = Arr::path($array, 'users.*.name');
        $this->assertSame(['matt', 'john'], $result);
    }

    public function testPathWithNestedWildcards(): void
    {
        $array = [
            'users' => [
                2 => ['interests' => ['hockey' => ['length' => 2]]],
            ]
        ];
        $result = Arr::path($array, 'users.*.interests.*.length');
        $this->assertSame([[2]], $result);
    }

    public function testPathWithArrayObject(): void
    {
        $array = ['object' => new ArrayObject(['iterator' => true])];
        $result = Arr::path($array, 'object.iterator');
        $this->assertTrue($result);
    }

    public function testPathWithDirectKeyExists(): void
    {
        $array = ['direct.key' => 'value', 'other' => 'data'];
        $result = Arr::path($array, 'direct.key');
        $this->assertSame('value', $result);
    }

    public function testPathTrimsDelimiters(): void
    {
        $array = ['level1' => ['level2' => 'value']];
        $result = Arr::path($array, ' .level1.level2. *', 'default');
        $this->assertSame('value', $result);
    }

    public static function pathProvider(): array
    {
        $array = [
            'foobar' => ['definition' => 'lost'],
            'ko7' => 'awesome',
            'users' => [
                1 => ['name' => 'matt'],
                2 => ['name' => 'john', 'interests' => ['hockey' => ['length' => 2], 'football' => []]],
                3 => 'frank',
            ],
            'object' => new ArrayObject(['iterator' => true]),
        ];

        return [
            // Direct keys
            [$array['foobar'], $array, 'foobar'],
            [$array['ko7'], $array, 'ko7'],
            
            // Nested paths
            [$array['foobar']['definition'], $array, 'foobar.definition'],
            [$array['users'][1]['name'], $array, 'users.1.name'],
            
            // Custom delimiter
            [$array['foobar']['definition'], $array, 'foobar/definition', null, '/'],
            
            // Default values
            [null, $array, 'foobar.alternatives', null],
            ['nothing', $array, 'ko7.alternatives', 'nothing'],
            [['far', 'wide'], $array, 'cheese.origins', ['far', 'wide']],
            
            // Wildcards
            [$array['users'], $array, 'users.*'],
            [null, $array, 'users.*.fans'],
            
            // ArrayObject
            [$array['object']['iterator'], $array, 'object.iterator'],
            
            // Non-existent paths
            ['default', $array, 'nonexistent.path', 'default'],
            [null, $array, 'users.999.name', null],
        ];
    }
}