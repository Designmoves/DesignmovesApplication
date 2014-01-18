<?php

namespace DesignmovesApplication;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\BootstrapListenerInterface,
    Feature\ConfigProviderInterface
{
    /**
     * Force lowercase request uri
     *
     * @param MvcEvent $event
     */
    public function forceLowercaseRequest(MvcEvent $event)
    {
        $application    = $event->getApplication();
        $serviceManager = $application->getServiceManager();
        $moduleOptions  = $serviceManager->get(__NAMESPACE__ . '\Options\ModuleOptions');

        if (true === $moduleOptions->getForceLowercaseRequest()) {
            $fullUrl = (string) $event->getRequest()->getUri()->normalize();
            if (strtolower($fullUrl) != $fullUrl) {
                $response = $event->getResponse();
                $response->setStatusCode(301);
                $response->getHeaders()->addHeaderLine('Location', strtolower($fullUrl));
                $response->send();
                // return $response->send(); does not work
                exit;
            }
        }
    }

    /**
     * Get auto loader config
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * On bootstrap event
     *
     * @param EventInterface $event
     */
    public function onBootstrap(EventInterface $event)
    {
        $this->forceLowercaseRequest($event);

        $application    = $event->getApplication();
        $serviceManager = $application->getServiceManager();
        $eventManager   = $application->getEventManager();

        $exceptionTemplateListener = $serviceManager->get(__NAMESPACE__ . '\Listener\ExceptionTemplateListener');
        $eventManager->attachAggregate($exceptionTemplateListener);

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
}
