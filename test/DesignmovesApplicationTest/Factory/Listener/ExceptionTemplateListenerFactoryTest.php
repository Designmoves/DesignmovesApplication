<?php

namespace DesignmovesApplicationTest\Factory\Listener;

use DesignmovesApplication\Factory\Listener\ExceptionTemplateListenerFactory;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Renderer\PhpRenderer;

/**
 * @coversDefaultClass DesignmovesApplication\Factory\Listener\ExceptionTemplateListenerFactory
 */
class ExceptionTemplateListenerFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ExceptionTemplateListenerFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function setUp()
    {
        $this->serviceManager = new ServiceManager;
        $this->factory        = new ExceptionTemplateListenerFactory();
    }

    /**
     * @covers ::createService
     */
    public function testCanCreateService()
    {
        $this->serviceManager->setService('Zend\View\Renderer\PhpRenderer', new PhpRenderer);

        $listener = $this->factory->createService($this->serviceManager);

        $this->assertInstanceOf('DesignmovesApplication\Listener\ExceptionTemplateListener', $listener);
    }
}
