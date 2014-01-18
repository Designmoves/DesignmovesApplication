<?php

namespace DesignmovesApplicationTest\Factory\Options;

use DesignmovesApplication\Factory\Options\ModuleOptionsFactory;
use PHPUnit_Framework_TestCase;

class ModuleOptionsFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ModuleOptionsFactory
     */
    protected $factory;

    public function setUp()
    {
        $this->factory = new ModuleOptionsFactory();
    }

    public function testFactoryReturnsCorrectInstance()
    {
        $config = array(
            'designmoves_application' => array(),
        );

        $serviceManagerMock = $this->getMockBuilder('Zend\ServiceManager\ServiceManager')
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $serviceManagerMock->expects($this->once())
                           ->method($this->equalTo('get'))
                           ->with($this->equalTo('Config'))
                           ->will($this->returnValue($config));

        $moduleOptions = $this->factory->createService($serviceManagerMock);
        $this->assertInstanceOf('DesignmovesApplication\Options\ModuleOptions', $moduleOptions);
    }
}
