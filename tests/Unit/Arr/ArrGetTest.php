<?php
namespace Modseven\Tests\Unit\Arr;

use Modseven\Tests\Support\TestCase;
use Modseven\Arr;

class ArrGetTest extends TestCase
{
    /**
     * @dataProvider getProvider
     * @param array $array Array to look in
     * @param string $key Key to look for
     * @param mixed $default What to return if $key isn't set
     * @param mixed $expected The expected value returned
     */
    public function testGet(array $array, string $key, $default, $expected): void
    {
        $this->assertSame(
            $expected,
            Arr::get($array, $key, $default)
        );
    }

    /**
     * Test that get() returns null when key doesn't exist and no default provided
     */
    public function testGetReturnsNullWhenKeyNotFoundAndNoDefault(): void
    {
        $array = ['existing' => 'value'];
        $result = Arr::get($array, 'nonexistent');
        
        $this->assertNull($result);
    }

    /**
     * Test that get() works with numeric string keys
     */
    public function testGetWithNumericStringKeys(): void
    {
        $array = ['0' => 'zero', '1' => 'one', '10' => 'ten'];
        
        $this->assertSame('zero', Arr::get($array, '0'));
        $this->assertSame('one', Arr::get($array, '1'));
        $this->assertSame('ten', Arr::get($array, '10'));
    }

    /**
     * Test edge case with object values - separate test for objects since assertSame checks identity
     */
    public function testGetWithObjectValues(): void
    {
        $object = new \stdClass();
        $object->property = 'value';
        
        $array = ['object' => $object];
        $result = Arr::get($array, 'object');
        
        $this->assertSame($object, $result); // Same object reference
        $this->assertInstanceOf(\stdClass::class, $result);
    }
    public function testGetWithEmptyStringKey(): void
    {
        $array = ['' => 'empty_key_value', 'normal' => 'normal_value'];
        
        $this->assertSame('empty_key_value', Arr::get($array, ''));
        $this->assertSame('default', Arr::get(['normal' => 'value'], '', 'default'));
    }

    /**
     * @return array<int, array{array, string, mixed, mixed}>
     */
    public static function getProvider(): array
    {
        return [
            // Basic indexed array access
            [['uno', 'dos', 'tres'], '1', null, 'dos'],
            [['uno', 'dos', 'tres'], '0', null, 'uno'],
            [['uno', 'dos', 'tres'], '2', null, 'tres'],
            
            // Basic associative array access
            [['we' => 'can', 'make' => 'change'], 'we', null, 'can'],
            [['we' => 'can', 'make' => 'change'], 'make', null, 'change'],
            
            // Non-existent keys return null when no default
            [['uno', 'dos', 'tres'], '10', null, null],
            [['we' => 'can', 'make' => 'change'], 'he', null, null],
            
            // Non-existent keys return default value
            [['we' => 'can', 'make' => 'change'], 'he', 'who', 'who'],
            [['we' => 'can', 'make' => 'change'], 'missing', ['arrays'], ['arrays']],
            [['test' => 'value'], 'missing', 0, 0],
            [['test' => 'value'], 'missing', false, false],
            [['test' => 'value'], 'missing', '', ''],
            
            // Test with complex values
            [['complex' => ['nested' => 'value']], 'complex', null, ['nested' => 'value']],
        ];
    }
}