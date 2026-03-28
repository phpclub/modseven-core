<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\Model;
use PHPUnit\Framework\TestCase;

/**
 * Minimal concrete model for testing Model::factory().
 */
class StubModel extends Model {}

/**
 * @covers \Modseven\Model
 */
class ModelTest extends TestCase
{
    public function testModelIsAbstract(): void
    {
        $rc = new \ReflectionClass(Model::class);
        $this->assertTrue($rc->isAbstract());
    }

    public function testFactoryReturnsInstance(): void
    {
        $model = Model::factory(StubModel::class);
        $this->assertInstanceOf(StubModel::class, $model);
        $this->assertInstanceOf(Model::class, $model);
    }
}
