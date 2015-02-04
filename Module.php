<?php
/**
 * Copyright (c) 2014 - 2015, Designmoves (http://www.designmoves.nl)
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of Designmoves nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace DesignmovesApplication;

use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\BootstrapListenerInterface,
    Feature\ConfigProviderInterface,
    Feature\InitProviderInterface
{
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
     * @param ModuleManagerInterface $moduleManager
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        $eventManager = $moduleManager->getEventManager();

        $identifier = 'Zend\Mvc\Application';
        $event      = MvcEvent::EVENT_BOOTSTRAP;
        $callback   = array($this, 'forceLowercaseRequest');
        $priority   = 100;
        $eventManager->getSharedManager()->attach($identifier, $event, $callback, $priority);
    }

    /**
     * On bootstrap event
     *
     * @param EventInterface $event
     */
    public function onBootstrap(EventInterface $event)
    {
        $application    = $event->getApplication();
        $eventManager   = $application->getEventManager();

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    /**
     * Force lowercase request uri
     * @link http://stackoverflow.com/a/14170913
     *
     * @param EventInterface $event
     */
    public function forceLowercaseRequest(EventInterface $event)
    {
        /* @var $event \Zend\Mvc\MvcEvent */

        // Ignore console requests
        if ($event->getRequest() instanceof ConsoleRequest) {
            return;
        }

        $application    = $event->getApplication();
        $serviceManager = $application->getServiceManager();
        $moduleOptions  = $serviceManager->get(__NAMESPACE__ . '\Options\ModuleOptions');

        if (false === $moduleOptions->getForceLowercaseRequest()) {
            // Nothing to do
            return;
        }

        $fullUrl = (string) $event->getRequest()->getUri()->normalize();
        if (strtolower($fullUrl) != $fullUrl) {
            $response = $event->getResponse();
            $response->setStatusCode(301);
            $response->getHeaders()->addHeaderLine('Location', strtolower($fullUrl));
            $response->sendHeaders();

            $application->getEventManager()->attach(
                MvcEvent::EVENT_ROUTE,
                function ($event) use ($response) {
                    $event->stopPropagation();

                    return $response;
                },
                10000
            );

            return $response;
        }
    }
}
