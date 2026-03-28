<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\Exception;
use Modseven\Validation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\Validation
 */
class ValidationTest extends TestCase
{
    // -------------------------------------------------------------------------
    // factory / constructor
    // -------------------------------------------------------------------------

    public function testFactoryReturnsInstance(): void
    {
        $v = Validation::factory(['foo' => 'bar']);
        $this->assertInstanceOf(Validation::class, $v);
    }

    // -------------------------------------------------------------------------
    // ArrayAccess
    // -------------------------------------------------------------------------

    public function testOffsetExists(): void
    {
        $v = Validation::factory(['name' => 'John', 'empty' => null]);
        $this->assertTrue(isset($v['name']));
        // null value: isset returns false in PHP
        $this->assertFalse(isset($v['missing']));
    }

    public function testOffsetGet(): void
    {
        $v = Validation::factory(['key' => 'value']);
        $this->assertSame('value', $v['key']);
    }

    public function testOffsetSetThrows(): void
    {
        $v = Validation::factory([]);
        $this->expectException(Exception::class);
        $v['foo'] = 'bar';
    }

    public function testOffsetUnsetThrows(): void
    {
        $v = Validation::factory(['foo' => 'bar']);
        $this->expectException(Exception::class);
        unset($v['foo']);
    }

    // -------------------------------------------------------------------------
    // data / copy
    // -------------------------------------------------------------------------

    public function testDataReturnsArray(): void
    {
        $data = ['a' => '1', 'b' => '2'];
        $v = Validation::factory($data);
        $this->assertSame($data, $v->data());
    }

    public function testCopyReturnsNewInstanceWithSameRules(): void
    {
        $v = Validation::factory(['name' => 'John']);
        $v->rule('name', 'notEmpty');

        $copy = $v->copy(['name' => '']);
        $this->assertNotSame($v, $copy);
        // original still valid
        $this->assertTrue($v->check());
        // copy should fail: empty name
        $this->assertFalse($copy->check());
    }

    // -------------------------------------------------------------------------
    // label / labels
    // -------------------------------------------------------------------------

    public function testLabelFluent(): void
    {
        $v = Validation::factory([]);
        $this->assertSame($v, $v->label('email', 'Email Address'));
    }

    public function testLabelsFluent(): void
    {
        $v = Validation::factory([]);
        $this->assertSame($v, $v->labels(['email' => 'Email', 'name' => 'Name']));
    }

    // -------------------------------------------------------------------------
    // bind
    // -------------------------------------------------------------------------

    public function testBindSingle(): void
    {
        $v = Validation::factory([]);
        $this->assertSame($v, $v->bind(':key', 'value'));
    }

    public function testBindArray(): void
    {
        $v = Validation::factory([]);
        $result = $v->bind([':a' => 1, ':b' => 2]);
        $this->assertSame($v, $result);
    }

    // -------------------------------------------------------------------------
    // rule / rules / check
    // -------------------------------------------------------------------------

    public function testCheckPassesWithValidData(): void
    {
        $v = Validation::factory(['name' => 'John']);
        $v->rule('name', 'notEmpty');
        $this->assertTrue($v->check());
    }

    public function testCheckFailsWithInvalidData(): void
    {
        $v = Validation::factory(['name' => '']);
        $v->rule('name', 'notEmpty');
        $this->assertFalse($v->check());
    }

    public function testCheckMultipleRulesFluent(): void
    {
        $v = Validation::factory(['email' => 'not-an-email']);
        $v->rules('email', [
            ['notEmpty'],
            ['email'],
        ]);
        $this->assertFalse($v->check());
    }

    public function testCheckPassesWhenFieldEmpty(): void
    {
        // Non-empty rules are skipped for empty values (except notEmpty/matches)
        $v = Validation::factory(['url' => '']);
        $v->rule('url', 'url');
        $this->assertTrue($v->check());
    }

    public function testCheckWithCustomCallable(): void
    {
        $v = Validation::factory(['value' => '42']);
        $v->rule('value', static fn($val) => is_numeric($val));
        $this->assertTrue($v->check());
    }

    // -------------------------------------------------------------------------
    // errors (raw — no file)
    // -------------------------------------------------------------------------

    public function testErrorsRawFormat(): void
    {
        $v = Validation::factory(['name' => '']);
        $v->rule('name', 'notEmpty');
        $v->check();
        $errors = $v->errors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertSame('notEmpty', $errors['name'][0]);
    }

    public function testErrorsEmptyWhenValid(): void
    {
        $v = Validation::factory(['name' => 'John']);
        $v->rule('name', 'notEmpty');
        $v->check();
        $this->assertSame([], $v->errors());
    }

    // -------------------------------------------------------------------------
    // error (manual)
    // -------------------------------------------------------------------------

    public function testManualError(): void
    {
        $v = Validation::factory(['field' => 'val']);
        $v->error('field', 'my_rule');
        $errors = $v->errors();
        $this->assertArrayHasKey('field', $errors);
        $this->assertSame('my_rule', $errors['field'][0]);
    }
}
