<?php

namespace DesignmovesApplicationTest\Options;

use DesignmovesApplication\Options\ModuleOptions;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;

class ModuleOptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ModuleOptions
     */
    protected $options;

    public function providerClassMethods()
    {
        return array(
            array('setForceLowercaseRequest'),
            array('getForceLowercaseRequest'),
        );
    }

    public function setUp()
    {
        $this->options = new ModuleOptions();
    }

    /**
     * @dataProvider providerClassMethods
     */
    public function testClassMethodExists($method)
    {
        $parentClassMethods = get_class_methods(get_parent_class($this->options));
        $classMethods       = array_diff(get_class_methods($this->options), $parentClassMethods);

        $errorMessage = sprintf('Class "%s" does not have a method with name "%s"',
            get_class($this->options),
            $method
        );
        $this->assertTrue(in_array($method, $classMethods), $errorMessage);
    }

    public function testClassMethodsCount()
    {
        $parentClassMethods = get_class_methods(get_parent_class($this->options));
        $classMethods       = array_diff(get_class_methods($this->options), $parentClassMethods);

        $errorMessage = sprintf('Class "%s" has a wrong amount of class methods', get_class($this->options));
        $this->assertCount(2, $classMethods, $errorMessage);
    }

    public function testForceLowercaseRequest()
    {
        $this->assertFalse($this->options->getForceLowercaseRequest());

        $this->options->setForceLowercaseRequest(true);
        $this->assertTrue($this->options->getForceLowercaseRequest());

        $this->options->setForceLowercaseRequest(false);
        $this->assertFalse($this->options->getForceLowercaseRequest());
    }

    public function testStrictModeIsEnabled()
    {
        $property = new ReflectionProperty($this->options, '__strictMode__');
        $property->setAccessible(true);

        $this->assertTrue($property->getValue($this->options));
    }
}
