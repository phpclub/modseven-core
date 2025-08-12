<?php
namespace Modseven\Tests\Unit\Arr;

use Modseven\Tests\Support\TestCase;
use Modseven\Arr;

class ArrIsAssocTest extends TestCase
{
    /**
     * @dataProvider isAssocProvider
     * @param array $array Array to check
     * @param bool $expected Is $array associative
     */
    public function testIsAssoc(array $array, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Arr::isAssoc($array)
        );
    }

    /**
     * Test the fundamental logic: sequential numeric keys vs non-sequential
     */
    public function testIsAssocLogic(): void
    {
        // Sequential numeric keys starting from 0 = indexed (not associative)
        $indexed = [0 => 'a', 1 => 'b', 2 => 'c'];
        $this->assertFalse(Arr::isAssoc($indexed));
        
        // Same content but created with [] syntax
        $indexedSyntax = ['a', 'b', 'c'];
        $this->assertFalse(Arr::isAssoc($indexedSyntax));
        
        // Non-sequential = associative
        $nonSequential = [0 => 'a', 2 => 'c', 1 => 'b']; // out of order
        $this->assertTrue(Arr::isAssoc($nonSequential));
        
        // Missing keys = associative
        $missingKeys = [0 => 'a', 2 => 'c']; // missing index 1
        $this->assertTrue(Arr::isAssoc($missingKeys));
        
        // Starting from non-zero = associative
        $nonZeroStart = [1 => 'a', 2 => 'b', 3 => 'c'];
        $this->assertTrue(Arr::isAssoc($nonZeroStart));
    }

    /**
     * Test with string keys
     */
    public function testIsAssocWithStringKeys(): void
    {
        $stringKeys = ['name' => 'John', 'age' => 30];
        $this->assertTrue(Arr::isAssoc($stringKeys));
        
        // Mix of string and numeric keys
        $mixed = ['name' => 'John', 0 => 'zero', 'age' => 30];
        $this->assertTrue(Arr::isAssoc($mixed));
    }

    /**
     * Test with empty array
     */
    public function testIsAssocWithEmptyArray(): void
    {
        $this->assertFalse(Arr::isAssoc([]));
    }

    /**
     * Test with single element arrays
     */
    public function testIsAssocWithSingleElement(): void
    {
        // Single indexed element (key 0)
        $this->assertFalse(Arr::isAssoc([0 => 'value']));
        $this->assertFalse(Arr::isAssoc(['value'])); // equivalent to above
        
        // Single element with non-zero numeric key
        $this->assertTrue(Arr::isAssoc([1 => 'value']));
        
        // Single element with string key
        $this->assertTrue(Arr::isAssoc(['key' => 'value']));
    }

    /**
     * Test arrays after manipulation
     */
    public function testIsAssocAfterArrayManipulation(): void
    {
        // Start with indexed array
        $array = ['a', 'b', 'c'];
        $this->assertFalse(Arr::isAssoc($array));
        
        // Remove middle element
        unset($array[1]); // now [0 => 'a', 2 => 'c']
        $this->assertTrue(Arr::isAssoc($array)); // no longer sequential
        
        // Re-index the array
        $array = array_values($array); // [0 => 'a', 1 => 'c']
        $this->assertFalse(Arr::isAssoc($array)); // sequential again
    }

    /**
     * @return array<int, array{array, bool}>
     */
    public static function isAssocProvider(): array
    {
        return [
            // False cases - indexed arrays (sequential numeric keys from 0)
            [[], false],
            [['one'], false],
            [['one', 'two', 'three'], false],
            [[0, 1, 2], false],
            [['a', 'b', 'c', 'd'], false],
            [[0 => 'zero', 1 => 'one', 2 => 'two'], false],
            
            // True cases - associative arrays (string keys)
            [['one' => 'o clock', 'two' => 'o clock', 'three' => 'o clock'], true],
            [['name' => 'John', 'age' => 30], true],
            [['key' => 'value'], true],
            
            // True cases - non-sequential numeric keys
            [[1 => 'one', 2 => 'two'], true], // doesn't start from 0
            [[0 => 'zero', 2 => 'two'], true], // gap in sequence
            [[2 => 'two', 0 => 'zero', 1 => 'one'], true], // out of order
            [[5 => 'five', 10 => 'ten'], true], // large gaps
            
            // True cases - mixed keys
            [[0 => 'zero', 'name' => 'John', 1 => 'one'], true],
            [['string_key' => 'value', 0 => 'numeric'], true],
            
            // True cases - special keys
            [['' => 'empty_string_key'], true],
            [[-1 => 'negative'], true],
            
            // True cases - complex structures
            [['nested' => ['array' => 'value']], true],
            [['callback' => 'function_name'], true],
        ];
    }
}