<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\Date;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\Date
 */
class DateTest extends TestCase
{
    // -------------------------------------------------------------------------
    // seconds / minutes
    // -------------------------------------------------------------------------

    public function testSecondsDefaultStep(): void
    {
        $result = Date::seconds();
        $this->assertCount(60, $result);
        $this->assertSame('00', $result[0]);
        $this->assertSame('59', $result[59]);
    }

    public function testSecondsCustomStep(): void
    {
        $result = Date::seconds(15);
        $this->assertCount(4, $result);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(15, $result);
        $this->assertArrayHasKey(30, $result);
        $this->assertArrayHasKey(45, $result);
    }

    public function testMinutesDelegatesToSeconds(): void
    {
        $this->assertSame(Date::seconds(5), Date::minutes(5));
    }

    // -------------------------------------------------------------------------
    // ampm
    // -------------------------------------------------------------------------

    /** @dataProvider providerAmpm */
    public function testAmpm(int $hour, string $expected): void
    {
        $this->assertSame($expected, Date::ampm($hour));
    }

    public static function providerAmpm(): array
    {
        return [
            [0,  'AM'],
            [11, 'AM'],
            [12, 'PM'],
            [23, 'PM'],
        ];
    }

    // -------------------------------------------------------------------------
    // adjust
    // -------------------------------------------------------------------------

    /** @dataProvider providerAdjust */
    public function testAdjust(int $hour, string $ampm, string $expected): void
    {
        $this->assertSame($expected, Date::adjust($hour, $ampm));
    }

    public static function providerAdjust(): array
    {
        return [
            // 12 AM => 00
            [12, 'am', '00'],
            // 1 AM stays 01
            [1,  'AM', '01'],
            // 3 PM => 15
            [3,  'pm', '15'],
            // 12 PM stays 12
            [12, 'PM', '12'],
        ];
    }

    // -------------------------------------------------------------------------
    // days
    // -------------------------------------------------------------------------

    public function testDaysJanuary(): void
    {
        $days = Date::days(1, 2023);
        $this->assertCount(31, $days);
        $this->assertSame('1', $days[1]);
        $this->assertSame('31', $days[31]);
    }

    public function testDaysFebruaryLeapYear(): void
    {
        $days = Date::days(2, 2024);
        $this->assertCount(29, $days);
    }

    public function testDaysFebruaryNonLeapYear(): void
    {
        $days = Date::days(2, 2023);
        $this->assertCount(28, $days);
    }

    // -------------------------------------------------------------------------
    // hours
    // -------------------------------------------------------------------------

    public function testHoursTwelveHour(): void
    {
        $hours = Date::hours();
        $this->assertCount(12, $hours);
        $this->assertSame('1', $hours[1]);
        $this->assertSame('12', $hours[12]);
        $this->assertArrayNotHasKey(0, $hours);
    }

    public function testHoursTwentyFourHour(): void
    {
        $hours = Date::hours(1, true);
        $this->assertCount(24, $hours);
        $this->assertSame('0', $hours[0]);
        $this->assertSame('23', $hours[23]);
    }

    // -------------------------------------------------------------------------
    // years
    // -------------------------------------------------------------------------

    public function testYearsExplicitRange(): void
    {
        $years = Date::years(2020, 2023);
        $this->assertSame(['2020' => '2020', '2021' => '2021', '2022' => '2022', '2023' => '2023'], $years);
    }

    // -------------------------------------------------------------------------
    // span
    // -------------------------------------------------------------------------

    public function testSpanSingleOutput(): void
    {
        $result = Date::span(0, Date::DAY, 'hours');
        $this->assertSame(24, $result);
    }

    public function testSpanFullOutput(): void
    {
        // Exactly 1 day + 2 hours
        $local = 1000000;
        $remote = $local - (Date::DAY + Date::HOUR * 2);
        $result = Date::span($remote, $local, 'days,hours');
        $this->assertIsArray($result);
        $this->assertSame(1, $result['days']);
        $this->assertSame(2, $result['hours']);
    }

    public function testSpanReturnsFalseForEmptyOutput(): void
    {
        $this->assertFalse(Date::span(0, 100, ''));
    }

    // -------------------------------------------------------------------------
    // fuzzy_span
    // -------------------------------------------------------------------------

    /** @dataProvider providerFuzzySpan */
    public function testFuzzySpan(int $offset, bool $past, string $expectedSubstring): void
    {
        $local = 1000000;
        $remote = $past ? ($local - $offset) : ($local + $offset);
        $result = Date::fuzzy_span($remote, $local);
        $this->assertStringContainsString($expectedSubstring, $result);
    }

    public static function providerFuzzySpan(): array
    {
        return [
            // within 1 minute — past
            [30,                             true,  'moments ago'],
            // a few minutes — past
            [Date::MINUTE * 5,               true,  'a few minutes ago'],
            // less than an hour — past
            [Date::MINUTE * 40,              true,  'less than an hour ago'],
            // a couple of hours — future
            [Date::HOUR * 2,                 false, 'in a couple of hours'],
            // about a day — past
            [Date::DAY + Date::HOUR,         true,  'about a day ago'],
            // less than a month — past
            [Date::WEEK * 3,                 true,  'less than a month ago'],
            // about a year — past
            [Date::YEAR + Date::MONTH,       true,  'about a year ago'],
        ];
    }

    // -------------------------------------------------------------------------
    // unix2dos / dos2unix roundtrip
    // -------------------------------------------------------------------------

    public function testUnixDosRoundtrip(): void
    {
        // Use a fixed timestamp well within DOS range (1980–2107)
        $ts = mktime(12, 30, 20, 6, 15, 2000);
        $dos = Date::unix2dos($ts);
        $back = Date::dos2unix($dos);

        // DOS timestamps have 2-second precision — compare within 2 seconds
        $this->assertLessThanOrEqual(2, abs($ts - $back));
    }

    public function testUnix2DosClampsBelowEpoch(): void
    {
        // Pre-1980 timestamps should be clamped
        $dos = Date::unix2dos(mktime(0, 0, 0, 1, 1, 1979));
        $this->assertSame(1 << 21 | 1 << 16, $dos);
    }

    // -------------------------------------------------------------------------
    // formattedTime
    // -------------------------------------------------------------------------

    public function testFormattedTimeWithExplicitFormat(): void
    {
        $result = Date::formattedTime('2023-06-15 12:00:00', 'Y-m-d', 'UTC');
        $this->assertSame('2023-06-15', $result);
    }

    public function testFormattedTimeWithTimestamp(): void
    {
        // @1000000000 is 2001-09-09 01:46:40 UTC
        $result = Date::formattedTime('@1000000000', 'Y-m-d', 'UTC');
        $this->assertSame('2001-09-09', $result);
    }

    // -------------------------------------------------------------------------
    // offset
    // -------------------------------------------------------------------------

    public function testOffsetUtcIsZero(): void
    {
        $offset = Date::offset('UTC', 'UTC', 'now');
        $this->assertSame(0, $offset);
    }

    public function testOffsetBetweenTimeZones(): void
    {
        // UTC+2 vs UTC: should differ by 7200 seconds
        $offset = Date::offset('Europe/Helsinki', 'UTC', '2023-01-15 12:00:00');
        $this->assertSame(7200, $offset);
    }
}
