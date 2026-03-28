<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\Log;
use Modseven\Log\Writer;
use Psr\Log\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Concrete writer that captures output in memory for testing.
 */
class CapturingWriter extends Writer
{
    public array $written = [];

    public function write(string $message): void
    {
        $this->written[] = $message;
    }
}

/**
 * @covers \Modseven\Log
 */
class LogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Reset the singleton before each test
        $ref = new \ReflectionProperty(Log::class, '_instance');
        $ref->setAccessible(true);
        $ref->setValue(null, null);
    }

    // -------------------------------------------------------------------------
    // instance
    // -------------------------------------------------------------------------

    public function testInstanceReturnsSingleton(): void
    {
        $a = Log::instance();
        $b = Log::instance();
        $this->assertInstanceOf(Log::class, $a);
        $this->assertSame($a, $b);
    }

    // -------------------------------------------------------------------------
    // attach / detach
    // -------------------------------------------------------------------------

    public function testAttachFluent(): void
    {
        $log    = Log::instance();
        $writer = new CapturingWriter();
        $result = $log->attach($writer);
        $this->assertSame($log, $result);
    }

    public function testDetachFluent(): void
    {
        $log    = Log::instance();
        $writer = new CapturingWriter();
        $log->attach($writer);
        $result = $log->detach($writer);
        $this->assertSame($log, $result);
    }

    public function testDetachedWriterReceivesNoMessages(): void
    {
        $log    = Log::instance();
        $writer = new CapturingWriter();
        $log->attach($writer);
        $log->detach($writer);
        $log->log(Log::INFO, 'hello');
        $this->assertCount(0, $writer->written);
    }

    // -------------------------------------------------------------------------
    // log
    // -------------------------------------------------------------------------

    public function testLogWritesToAttachedWriter(): void
    {
        $log    = Log::instance();
        $writer = new CapturingWriter();
        $log->attach($writer);

        $log->log(Log::INFO, 'test message');

        $this->assertCount(1, $writer->written);
        $this->assertStringContainsString('info', $writer->written[0]);
        $this->assertStringContainsString('test message', $writer->written[0]);
    }

    public function testLogInterpolatesContext(): void
    {
        $log    = Log::instance();
        $writer = new CapturingWriter();
        $log->attach($writer);

        $log->log(Log::DEBUG, 'Hello, {name}!', ['name' => 'World']);

        $this->assertStringContainsString('Hello, World!', $writer->written[0]);
    }

    public function testLogWithLevelFilterOnlyWritesMatchingLevel(): void
    {
        $log    = Log::instance();
        $writer = new CapturingWriter();
        // Only accept ERROR messages
        $log->attach($writer, [Log::ERROR]);

        $log->log(Log::INFO,  'info msg');
        $log->log(Log::ERROR, 'error msg');

        $this->assertCount(1, $writer->written);
        $this->assertStringContainsString('error msg', $writer->written[0]);
    }

    public function testLogThrowsForInvalidLevel(): void
    {
        $log = Log::instance();
        $this->expectException(InvalidArgumentException::class);
        $log->log('invalid_level', 'message');
    }

    // -------------------------------------------------------------------------
    // PSR-3 convenience methods
    // -------------------------------------------------------------------------

    public function testEmergencyMethod(): void
    {
        $log    = Log::instance();
        $writer = new CapturingWriter();
        $log->attach($writer);

        $log->emergency('critical failure');

        $this->assertCount(1, $writer->written);
        $this->assertStringContainsString('emergency', $writer->written[0]);
    }
}
