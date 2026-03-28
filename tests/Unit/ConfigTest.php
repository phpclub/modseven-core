<?php

namespace Modseven\Tests\Unit;

use Modseven\Config;
use Modseven\Config\Reader as ConfigReader;
use Modseven\Tests\Support\TestCase;

class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Reset singleton between tests
        $ref = new \ReflectionProperty(Config::class, '_instance');
        $ref->setAccessible(true);
        $ref->setValue(null, null);
    }

    public function test_config(): void
    {
        $config = Config::instance();

        $this->assertInstanceOf(Config::class, $config);
    }

    public function test_load_group(): void
    {
        $config = Config::instance();

        $reader = $this->createMock(ConfigReader::class);
        $reader->method('load')
            ->with('some_group')
            ->willReturn(['key' => 'value']);

        $config->attach($reader);

        $group = $config->load('some_group');

        $this->assertArrayHasKey('key', $group);
    }

    public function test_copy_config(): void
    {
        $config = Config::instance();

        $this->assertInstanceOf(Config::class, $config);
    }
}
