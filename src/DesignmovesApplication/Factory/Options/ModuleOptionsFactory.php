<?php

namespace DesignmovesApplication\Factory\Options;

use DesignmovesApplication\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ModuleOptionsFactory implements FactoryInterface
{
    /**
     * @param  ServiceLocatorInterface $serviceManager
     * @return ModuleOptions
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $config = $serviceManager->get('Config');

        $moduleConfig = array();
        if (isset($config['designmoves_application'])) {
            $moduleConfig = $config['designmoves_application'];
        }

        return new ModuleOptions($moduleConfig);
    }
}
