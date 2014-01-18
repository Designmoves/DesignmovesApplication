<?php

namespace DesignmovesApplicationTest\Options;

use DesignmovesApplication\Options\ModuleOptions;
use PHPUnit_Framework_TestCase;

class ModuleOptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;

    public function providerClassMethods()
    {
        return array(
            array('setForceLowercaseRequest'),
            array('getForceLowercaseRequest'),
        );
    }

    public function setUp()
    {
        $this->moduleOptions = new ModuleOptions();
    }

    /**
     * @dataProvider providerClassMethods
     */
    public function testClassMethods($method)
    {
        $parentClassMethods = get_class_methods(get_parent_class($this->moduleOptions));
        $classMethods       = array_diff(get_class_methods($this->moduleOptions), $parentClassMethods);

        $errorMessage = sprintf('Class "%s" does not have a method with name "%s"',
            get_class($this->moduleOptions),
            $method
        );
        $this->assertTrue(in_array($method, $classMethods), $errorMessage);
    }

    public function testClassMethodsCount()
    {
        $parentClassMethods = get_class_methods(get_parent_class($this->moduleOptions));
        $classMethods       = array_diff(get_class_methods($this->moduleOptions), $parentClassMethods);

        $errorMessage = sprintf('Class "%s" has a wrong amount of class methods', get_class($this->moduleOptions));
        $this->assertCount(2, $classMethods, $errorMessage);
    }

    public function testForceLowercaseRequest()
    {
        $this->assertFalse($this->moduleOptions->getForceLowercaseRequest());

        $this->moduleOptions->setForceLowercaseRequest(true);
        $this->assertTrue($this->moduleOptions->getForceLowercaseRequest());

        $this->moduleOptions->setForceLowercaseRequest(false);
        $this->assertFalse($this->moduleOptions->getForceLowercaseRequest());
    }

    public function testWithCorrectConfig()
    {
        $this->moduleOptions->setFromArray(array(
            'force_lowercase_request' => true,
        ));
        $this->assertTrue($this->moduleOptions->getForceLowercaseRequest());

        $this->moduleOptions->setFromArray(array(
            'force_lowercase_request' => false,
        ));
        $this->assertFalse($this->moduleOptions->getForceLowercaseRequest());
    }

    /**
     * @expectedException Zend\Stdlib\Exception\BadMethodCallException
     */
    public function testWithWrongConfig()
    {
        $this->moduleOptions->setFromArray(array(
            'non_existing_key' => false,
        ));
    }
}
