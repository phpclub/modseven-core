<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\Exception
 */
class ExceptionTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    public function testConstructorSetsMessage(): void
    {
        $e = new Exception('Something went wrong');
        $this->assertSame('Something went wrong', $e->getMessage());
    }

    public function testConstructorSubstitutesVariables(): void
    {
        $e = new Exception('Hello, :name', [':name' => 'World']);
        $this->assertSame('Hello, World', $e->getMessage());
    }

    public function testConstructorSetsIntCode(): void
    {
        $e = new Exception('Error', null, 42);
        $this->assertSame(42, $e->getCode());
    }

    public function testConstructorSetsStringCode(): void
    {
        // String codes are preserved in $this->code but cast to int for parent
        $e = new Exception('Error', null, 'E_TEST');
        $this->assertSame('E_TEST', $e->getCode());
    }

    public function testConstructorSetsPreviousException(): void
    {
        $prev = new \RuntimeException('root cause');
        $e    = new Exception('Wrapper', null, 0, $prev);
        $this->assertSame($prev, $e->getPrevious());
    }

    public function testIsInstanceOfBaseException(): void
    {
        $e = new Exception('test');
        $this->assertInstanceOf(\Exception::class, $e);
    }

    // -------------------------------------------------------------------------
    // php_errors array
    // -------------------------------------------------------------------------

    public function testPhpErrorsContainsCommonCodes(): void
    {
        $this->assertArrayHasKey(E_ERROR, Exception::$php_errors);
        $this->assertArrayHasKey(E_WARNING, Exception::$php_errors);
        $this->assertArrayHasKey(E_NOTICE, Exception::$php_errors);
        // E_STRICT (2048) must NOT be present — deprecated/removed in PHP 8.4
        $this->assertArrayNotHasKey(2048, Exception::$php_errors);
    }
}
