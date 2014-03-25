<?php

namespace DesignmovesApplicationTest\Factory\Options;

use DesignmovesApplication\Factory\Options\ModuleOptionsFactory;
use PHPUnit_Framework_TestCase;
use Zend\Config\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * @coversDefaultClass DesignmovesApplication\Factory\Options\ModuleOptionsFactory
 */
class ModuleOptionsFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ModuleOptionsFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function setUp()
    {
        $this->factory        = new ModuleOptionsFactory;
        $this->serviceManager = new ServiceManager;
    }

    /**
     * @covers ::createService
     */
    public function testCanCreateService()
    {
        $config = new Config(array(
            'designmoves_application' => array(),
        ));
        $this->serviceManager->setService('Config', $config);

        $moduleOptions = $this->factory->createService($this->serviceManager);

        $this->assertInstanceOf('DesignmovesApplication\Options\ModuleOptions', $moduleOptions);
    }
}
