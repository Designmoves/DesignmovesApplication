<?php
/**
 * Copyright (c) 2014, Designmoves http://www.designmoves.nl
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

namespace DesignmovesApplicationTest;

use DesignmovesApplication\Module;
use DesignmovesApplication\Listener\ExceptionTemplateListener;
use DesignmovesApplication\Options\ModuleOptions;
use PHPUnit_Framework_TestCase;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Response as ConsoleResponse;
use Zend\EventManager\EventManager;
use Zend\EventManager\SharedEventManager;
use Zend\Http\PhpEnvironment\Response as HttpResponse;
use Zend\Http\Request as HttpRequest;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Uri\Http as HttpUri;
use Zend\View\Renderer\PhpRenderer;

/**
 * @coversDefaultClass DesignmovesApplication\Module
 */
class ModuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Module
     */
    protected $module;

    public function setUp()
    {
        $this->module = new Module;
    }

    /**
     * - original request uri
     * - expected status code
     * - expected header line
     */
    public function providerRequestUri()
    {
        return array(
            array('/'       , 200, ''),
            array('/foo-bar', 200, ''),
            array('/Foo'    , 301, 'Location: /foo'),
            array('Bar'     , 301, 'Location: bar'),
        );
    }

    /**
     * @covers ::getAutoloaderConfig
     */
    public function testCanGetAutoloaderConfig()
    {
        $autoloaderConfig = $this->module->getAutoloaderConfig();

        $this->assertInternalType('array', $autoloaderConfig);
        $this->assertArrayHasKey('Zend\Loader\ClassMapAutoloader', $autoloaderConfig);
        $this->assertArrayHasKey('Zend\Loader\StandardAutoloader', $autoloaderConfig);
    }

    /**
     * @cover ::getConfig
     */
    public function testCanGetConfig()
    {
        $this->assertInternalType('array', $this->module->getConfig());
    }

    /**
     * @covers ::init
     */
    public function testInitAttachesForceLowercaseRequestListener()
    {
        $eventManager = new EventManager;
        $eventManager->setSharedManager(new SharedEventManager);

        $moduleManager = new ModuleManager(array());
        $moduleManager->setEventManager($eventManager);

        $this->module->init($moduleManager);

        $id               = 'Zend\Mvc\Application';
        $event            = MvcEvent::EVENT_BOOTSTRAP;
        $listeners        = $eventManager->getSharedManager()->getListeners($id, $event);
        $expectedCallback = array($this->module, 'forceLowercaseRequest');
        $expectedPriority = 100;

        $found = false;
        foreach ($listeners as $listener) {
            $callback = $listener->getCallback();
            if ($callback === $expectedCallback) {
                if ($listener->getMetadatum('priority') == $expectedPriority) {
                    $found = true;
                    break;
                }
            }
        }

        $this->assertTrue($found, 'Listener not found');
    }

    /**
     * @covers ::onBootstrap
     * @uses   DesignmovesApplication\Listener\ExceptionTemplateListener
     */
    public function testOnBootstrapAttachesExceptionTemplateListener()
    {
        $eventManagerMock = $this->getMock('Zend\EventManager\EventManager');

        $serviceManager = new ServiceManager;
        $serviceManager->setService('EventManager', $eventManagerMock);
        $serviceManager->setService('Request', new HttpRequest);
        $serviceManager->setService('Response', new HttpResponse);

        $exceptionTemplateListener = new ExceptionTemplateListener(new PhpRenderer);
        $serviceManager->setService(
            'DesignmovesApplication\Listener\ExceptionTemplateListener',
            $exceptionTemplateListener
        );

        $mvcEvent    = new MvcEvent;
        $application = new Application(array(), $serviceManager);
        $mvcEvent->setApplication($application);

        $eventManagerMock->expects($this->at(0))
                         ->method($this->equalTo('attach'))
                         ->with($exceptionTemplateListener);

        $this->module->onBootstrap($mvcEvent);
    }

    /**
     * @covers       ::forceLowercaseRequest
     * @dataProvider providerRequestUri
     * @uses         DesignmovesApplication\Options\ModuleOptions
     */
    public function testCanForceLowercaseRequest($requestUri, $expectedStatusCode, $expectedHeaderLine)
    {
        $request  = new HttpRequest;
        $response = new HttpResponse;

        $event = new MvcEvent;
        $event->setRequest($request);
        $event->setResponse($response);

        $serviceManager = new ServiceManager;
        $serviceManager->setService('EventManager', new EventManager);
        $serviceManager->setService('Request'     , $request);
        $serviceManager->setService('Response'    , $response);

        $application = new Application(array(), $serviceManager);
        $event->setApplication($application);

        $moduleOptions = new ModuleOptions;
        $serviceManager->setService('DesignmovesApplication\Options\ModuleOptions', $moduleOptions);

        $request->setUri(new HttpUri($requestUri));
        $this->module->forceLowercaseRequest($event);

        $this->assertSame($expectedStatusCode, $response->getStatusCode());

        $headerLine = '';
        $headers    = $response->getHeaders();
        foreach ($headers as $header) {
            $headerLine .= $header->toString();
        }
        $this->assertSame($expectedHeaderLine, $headerLine);

        if (301 == $expectedStatusCode) {
            $this->assertSame('', $response->getContent());
        }
    }

    /**
     * @covers ::forceLowercaseRequest
     */
    public function testForceLowercaseRequestIgnoresConsoleRequest()
    {
        $consoleRequest  = new ConsoleRequest;
        $consoleResponse = new ConsoleResponse;

        $event = new MvcEvent;
        $event->setRequest($consoleRequest);
        $event->setResponse($consoleResponse);

        $serviceManager = new ServiceManager;
        $serviceManager->setService('EventManager', new EventManager);
        $serviceManager->setService('Request'     , $consoleRequest);
        $serviceManager->setService('Response'    , $consoleResponse);

        $application = new Application(array(), $serviceManager);
        $event->setApplication($application);

        $consoleRequest->setScriptName('foo');
        $this->module->forceLowercaseRequest($event);

        $this->assertSame(0, $consoleResponse->getErrorLevel());
        $this->assertSame("\r\n", $consoleResponse->toString());
    }
}
