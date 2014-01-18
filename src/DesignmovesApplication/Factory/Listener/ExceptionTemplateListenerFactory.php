<?php

namespace DesignmovesApplication\Factory\Listener;

use DesignmovesApplication\Listener\ExceptionTemplateListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ExceptionTemplateListenerFactory implements FactoryInterface
{
    /**
     * Create exception template listener
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ExceptionTemplateListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $renderer = $serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        return new ExceptionTemplateListener($renderer);
    }
}
