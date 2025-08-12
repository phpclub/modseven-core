<?php
namespace Modseven\Tests\Unit\Arr;

use Modseven\Tests\Support\TestCase;
use Modseven\Arr;
use ArrayObject;
use ArrayIterator;
use stdClass;
use SplFileInfo;

class ArrIsArrayTest extends TestCase
{
    /**
     * @dataProvider isArrayProvider
     * @param mixed $value Value to check
     * @param bool $expected Is $value an array?
     */
    public function testIsArray($value, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Arr::isArray($value)
        );
    }

    /**
     * Test with various Traversable objects
     */
    public function testIsArrayWithTraversableObjects(): void
    {
        $array = ['one', 'two', 'three'];
        
        // Test with ArrayObject
        $arrayObject = new ArrayObject($array);
        $this->assertTrue(Arr::isArray($arrayObject));
        
        // Test with ArrayIterator
        $arrayIterator = new ArrayIterator($array);
        $this->assertTrue(Arr::isArray($arrayIterator));
        
        // Test with custom Traversable implementation
        $customTraversable = new class implements \Iterator {
            private array $data = ['a', 'b', 'c'];
            private int $position = 0;
            
            public function rewind(): void { $this->position = 0; }
            public function current(): mixed { return $this->data[$this->position]; }
            public function key(): mixed { return $this->position; }
            public function next(): void { ++$this->position; }
            public function valid(): bool { return isset($this->data[$this->position]); }
        };
        
        $this->assertTrue(Arr::isArray($customTraversable));
    }

    /**
     * Test with objects that are NOT Traversable
     */
    public function testIsArrayWithNonTraversableObjects(): void
    {
        // Standard object
        $stdClass = new stdClass();
        $this->assertFalse(Arr::isArray($stdClass));
        
        // Object with array-like properties but not Traversable
        $objectWithArrayProps = new class {
            public $items = ['a', 'b', 'c'];
        };
        $this->assertFalse(Arr::isArray($objectWithArrayProps));
        
        // SplFileInfo is not Traversable
        $fileInfo = new SplFileInfo(__FILE__);
        $this->assertFalse(Arr::isArray($fileInfo));
    }

    /**
     * @return array<int, array{mixed, bool}>
     */
    public static function isArrayProvider(): array
    {
        $basicArray = ['one', 'two', 'three'];
        
        return [
            // True cases - actual arrays
            [[], true],
            [['one', 'two', 'three'], true],
            [['key' => 'value'], true],
            [[1, 2, 3], true],
            [['nested' => ['array' => 'value']], true],
            
            // True cases - Traversable objects
            [new ArrayObject($basicArray), true],
            [new ArrayIterator($basicArray), true],
            
            // False cases - primitives
            ['not an array', false],
            [123, false],
            [12.34, false],
            [true, false],
            [false, false],
            [null, false],
            
            // False cases - non-Traversable objects
            [new stdClass(), false],
            
            // Edge cases
            ['', false], // empty string
            [0, false],  // zero
        ];
    }
}