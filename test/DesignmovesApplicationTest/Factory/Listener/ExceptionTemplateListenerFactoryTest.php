<?php

namespace DesignmovesApplicationTest\Factory\Listener;

use DesignmovesApplication\Factory\Listener\ExceptionTemplateListenerFactory;
use PHPUnit_Framework_TestCase;

class PageListenerFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ExceptionTemplateListenerFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $serviceManagerMock;

    public function setUp()
    {
        $this->serviceManagerMock = $this->getMockBuilder('Zend\ServiceManager\ServiceManager')
                                         ->disableOriginalConstructor()
                                         ->getMock();

        $this->factory = new ExceptionTemplateListenerFactory();
    }

    public function testFactoryReturnsCorrectInstance()
    {
        $rendererMock = $this->getMockBuilder('Zend\View\Renderer\PhpRenderer')
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->serviceManagerMock
             ->expects($this->once())
             ->method('get')
             ->with($this->equalTo('Zend\View\Renderer\PhpRenderer'))
             ->will($this->returnValue($rendererMock));

        $listener = $this->factory->createService($this->serviceManagerMock);
        $this->assertInstanceOf('DesignmovesApplication\Listener\ExceptionTemplateListener', $listener);
    }
}
