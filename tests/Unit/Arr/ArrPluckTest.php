<?php
namespace Modseven\Tests\Unit\Arr;

use Modseven\Tests\Support\TestCase;
use Modseven\Arr;

class ArrPluckTest extends TestCase
{
    /**
     * @dataProvider pluckProvider
     * @param array $array List of arrays to pluck from
     * @param string $key Key to pluck
     * @param array $expected Expected result
     */
    public function testPluck(array $array, string $key, array $expected): void
    {
        $result = Arr::pluck($array, $key);

        $this->assertSame(count($expected), count($result));
        $this->assertSame($expected, $result);
    }

    /**
     * Test pluck with empty array
     */
    public function testPluckWithEmptyArray(): void
    {
        $result = Arr::pluck([], 'any_key');
        $this->assertSame([], $result);
    }

    /**
     * Test pluck when key doesn't exist in any sub-arrays
     */
    public function testPluckWithNonExistentKey(): void
    {
        $array = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];
        
        $result = Arr::pluck($array, 'nonexistent');
        $this->assertSame([], $result);
    }

    /**
     * Test pluck with mixed data - some rows have key, some don't
     */
    public function testPluckWithMixedData(): void
    {
        $array = [
            ['id' => 1, 'name' => 'John'],
            ['name' => 'Jane'], // no 'id' key
            ['id' => 3, 'name' => 'Bob'],
            ['age' => 25], // no 'id' or 'name' keys
        ];

        $ids = Arr::pluck($array, 'id');
        $this->assertSame([1, 3], $ids);

        $names = Arr::pluck($array, 'name');
        $this->assertSame(['John', 'Jane', 'Bob'], $names);
    }

    /**
     * Test pluck preserves order
     */
    public function testPluckPreservesOrder(): void
    {
        $array = [
            ['priority' => 'high', 'task' => 'urgent'],
            ['priority' => 'low', 'task' => 'later'],
            ['priority' => 'medium', 'task' => 'soon'],
        ];

        $priorities = Arr::pluck($array, 'priority');
        $this->assertSame(['high', 'low', 'medium'], $priorities);
    }

    /**
     * Test pluck with various value types
     */
    public function testPluckWithVariousValueTypes(): void
    {
        $array = [
            ['value' => 123],
            ['value' => 'string'],
            ['value' => true],
            ['value' => null], // This will be skipped by isset()
            ['value' => ['nested', 'array']],
            ['value' => new \stdClass()],
        ];

        $values = Arr::pluck($array, 'value');
        
        // Note: null values are skipped by isset() check in pluck()
        $this->assertCount(5, $values); // 6 - 1 (null skipped)
        $this->assertSame(123, $values[0]);
        $this->assertSame('string', $values[1]);
        $this->assertTrue($values[2]);
        // values[3] would be null, but it's skipped
        $this->assertSame(['nested', 'array'], $values[3]); // shifted index
        $this->assertInstanceOf(\stdClass::class, $values[4]); // shifted index
    }

    /**
     * Test pluck with numeric keys
     */
    public function testPluckWithNumericKeys(): void
    {
        $array = [
            [0 => 'first', 1 => 'second'],
            [0 => 'third', 1 => 'fourth'],
            [1 => 'fifth'], // missing key 0
        ];

        $firsts = Arr::pluck($array, '0');
        $this->assertSame(['first', 'third'], $firsts);

        $seconds = Arr::pluck($array, '1');
        $this->assertSame(['second', 'fourth', 'fifth'], $seconds);
    }

    /**
     * @return array<int, array{array, string, array}>
     */
    public static function pluckProvider(): array
    {
        return [
            // Basic case from Koseven test
            [
                [
                    ['id' => 20, 'name' => 'John Smith'],
                    ['name' => 'Linda'], // missing 'id'
                    ['id' => 25, 'name' => 'Fred'],
                ],
                'id',
                [20, 25]
            ],
            
            // Additional test cases
            [
                [
                    ['category' => 'fruit', 'name' => 'apple'],
                    ['category' => 'vegetable', 'name' => 'carrot'],
                    ['category' => 'fruit', 'name' => 'banana'],
                ],
                'category',
                ['fruit', 'vegetable', 'fruit']
            ],
            
            // Test with all rows having the key
            [
                [
                    ['status' => 'active'],
                    ['status' => 'inactive'],
                    ['status' => 'pending'],
                ],
                'status',
                ['active', 'inactive', 'pending']
            ],
            
            // Test with no rows having the key
            [
                [
                    ['name' => 'John'],
                    ['name' => 'Jane'],
                ],
                'missing_key',
                []
            ],
            
            // Test with single row
            [
                [
                    ['single' => 'value']
                ],
                'single',
                ['value']
            ],
            
            // Test with empty sub-arrays
            [
                [
                    ['key' => 'value1'],
                    [], // empty array
                    ['key' => 'value2'],
                ],
                'key',
                ['value1', 'value2']
            ],
        ];
    }
}