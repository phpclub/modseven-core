<?php

namespace Modseven\Tests\Unit;

use Modseven\Controller;
use Modseven\Tests\Support\TestCase;

class ControllerTest extends TestCase
{
    public function test_controller_is_abstract(): void
    {
        $ref = new \ReflectionClass(Controller::class);
        $this->assertTrue($ref->isAbstract());
    }
}
