<?php
namespace Modseven\Tests\Unit\Arr;

use Modseven\Tests\Support\TestCase;
use Modseven\Arr;

class ArrRangeTest extends TestCase
{
    /**
     * @dataProvider rangeProvider
     * @param int $step The step between each value in the array
     * @param int $max The max value of the range (inclusive)
     */
    public function testRange(int $step, int $max): void
    {
        $range = Arr::range($step, $max);

        // Check expected count
        $expectedCount = (int) floor($max / $step);
        $this->assertSame($expectedCount, count($range));

        // Verify range content and structure
        $current = $step;
        foreach ($range as $key => $value) {
            $this->assertSame($key, $value, "Key should equal value in range");
            $this->assertSame($current, $key, "Key should match expected progression");
            $this->assertLessThanOrEqual($max, $key, "Key should not exceed max value");
            $current += $step;
        }
    }

    /**
     * Test default parameters
     */
    public function testRangeWithDefaults(): void
    {
        $range = Arr::range(); // step=10, max=100
        
        $this->assertCount(10, $range);
        $this->assertSame([10 => 10, 20 => 20, 30 => 30, 40 => 40, 50 => 50, 60 => 60, 70 => 70, 80 => 80, 90 => 90, 100 => 100], $range);
    }

    /**
     * Test edge case with step larger than max
     */
    public function testRangeWithStepLargerThanMax(): void
    {
        $range = Arr::range(50, 25); // step > max
        $this->assertEmpty($range);
    }

    /**
     * Test edge case with step equal to max
     */
    public function testRangeWithStepEqualToMax(): void
    {
        $range = Arr::range(25, 25); // step == max
        $this->assertSame([25 => 25], $range);
    }

    /**
     * Test edge case with zero or negative step
     */
    public function testRangeWithInvalidStep(): void
    {
        // Zero step
        $range = Arr::range(0, 100);
        $this->assertEmpty($range);
        
        // Negative step
        $range = Arr::range(-5, 100);
        $this->assertEmpty($range);
    }

    /**
     * Test edge case with zero max
     */
    public function testRangeWithZeroMax(): void
    {
        $range = Arr::range(10, 0);
        $this->assertEmpty($range);
    }

    /**
     * Test with large ranges
     */
    public function testRangeWithLargeValues(): void
    {
        $range = Arr::range(1000, 5000);
        
        $this->assertCount(5, $range);
        $this->assertArrayHasKey(1000, $range);
        $this->assertArrayHasKey(5000, $range);
        $this->assertSame(1000, $range[1000]);
        $this->assertSame(5000, $range[5000]);
    }

    /**
     * Test fractional results (max not divisible by step)
     */
    public function testRangeWithFractionalResults(): void
    {
        $range = Arr::range(3, 10); // 3, 6, 9 (10 is not included as 10/3 = 3.33)
        
        $expected = [3 => 3, 6 => 6, 9 => 9];
        $this->assertSame($expected, $range);
    }

    /**
     * @return array<int, array{int, int}>
     */
    public static function rangeProvider(): array
    {
        return [
            // Basic cases from Koseven tests
            [1, 2],   // [1 => 1, 2 => 2]
            [1, 100], // [1 => 1, 2 => 2, ..., 100 => 100] 
            [25, 10], // [] (empty, step > max)
            
            // Additional test cases
            [5, 20],  // [5 => 5, 10 => 10, 15 => 15, 20 => 20]
            [10, 50], // [10 => 10, 20 => 20, 30 => 30, 40 => 40, 50 => 50]
            [7, 21],  // [7 => 7, 14 => 14, 21 => 21]
            [3, 10],  // [3 => 3, 6 => 6, 9 => 9] (10 not included)
            [1, 1],   // [1 => 1]
            [2, 2],   // [2 => 2]
        ];
    }
}