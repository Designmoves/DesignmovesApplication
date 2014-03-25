<?php

namespace DesignmovesApplicationTest\Options;

use DesignmovesApplication\Options\ModuleOptions;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass DesignmovesApplication\Options\ModuleOptions
 */
class ModuleOptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ModuleOptions
     */
    protected $options;

    public function setUp()
    {
        $this->options = new ModuleOptions;
    }

    public function testStrictModeIsEnabled()
    {
        $this->assertTrue(self::readAttribute($this->options, '__strictMode__'));
    }

    /**
     * @covers ::getForceLowercaseRequest
     */
    public function testGetForceLowercaseRequestDefaultsToFalse()
    {
        $this->assertFalse($this->options->getForceLowercaseRequest());
    }

    /**
     * @covers ::setForceLowercaseRequest
     * @covers ::getForceLowercaseRequest
     */
    public function testCanSetForceLowercaseRequest()
    {
        $this->assertFalse($this->options->getForceLowercaseRequest());

        $this->options->setForceLowercaseRequest(true);
        $this->assertTrue($this->options->getForceLowercaseRequest());

        $this->options->setForceLowercaseRequest(false);
        $this->assertFalse($this->options->getForceLowercaseRequest());
    }
}
