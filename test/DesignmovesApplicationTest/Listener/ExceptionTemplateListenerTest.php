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

namespace DesignmovesApplicationTest\Listener;

use Exception;
use DesignmovesApplication\Listener\ExceptionTemplateListener;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use Zend\EventManager\EventManager;
use Zend\Http\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

/**
 * @coversDefaultClass DesignmovesApplication\Listener\ExceptionTemplateListener
 * @uses               DesignmovesApplication\Listener\ExceptionTemplateListener
 */
class ExceptionTemplateListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zend\Mvc\MvcEvent
     */
    protected $event;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var Zend\View\Renderer\PhpRenderer
     */
    protected $renderer;

    /**
     * @var ExceptionTemplateListener
     */
    protected $listener;

    public function providerCustomStatusCodes()
    {
        return array(
            array(0),
            array(809),
            array(23000),
        );
    }

    public function providerDefaultStatusCodes()
    {
        return array(
            array(301),
            array(302),
            array(303),
            array(404),
            array(410),
            array(500),
            array(503),
        );
    }

    public function setUp()
    {
        $this->event    = new MvcEvent();
        $this->renderer = new PhpRenderer();
        $this->listener = new ExceptionTemplateListener($this->renderer);
    }

    /**
     * @covers ::__construct
     */
    public function testRendererIsSetOnConstruct()
    {
        $this->assertSame($this->renderer, self::readAttribute($this->listener, 'renderer'));
    }

    /**
     * @covers ::attach
     */
    public function testAttachesPrepareExceptionViewModelListenerOnEventDispatchError()
    {
        $eventManager = new EventManager();
        $eventManager->attach($this->listener);

        $listeners        = $eventManager->getListeners(MvcEvent::EVENT_DISPATCH_ERROR);
        $expectedCallback = array($this->listener, 'prepareExceptionViewModel');
        $expectedPriority = 1;

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
     * @covers ::attach
     */
    public function testAttachesPrepareExceptionViewModelListenerOnEventRenderError()
    {
        $eventManager = new EventManager();
        $eventManager->attach($this->listener);

        $listeners        = $eventManager->getListeners(MvcEvent::EVENT_RENDER_ERROR);
        $expectedCallback = array($this->listener, 'prepareExceptionViewModel');
        $expectedPriority = 1;

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
     * @covers ::prepareExceptionViewModel
     */
    public function testPrepareExceptionViewModelReturnsEarlyWhenErrorIsEmpty()
    {
        $returnValue = $this->listener->prepareExceptionViewModel($this->event);
        $this->assertNull($returnValue);
    }

    /**
     * @covers       ::prepareExceptionViewModel
     * @dataProvider providerCustomStatusCodes
     */
    public function testPrepareExceptionViewModelSetsStatusCodeTo500WhenCustomStatusCodeIsGiven($statusCode)
    {
        $exception = new Exception('Custom exception', $statusCode);
        $this->event->setParam('exception', $exception);

        $this->event->setError(Application::ERROR_EXCEPTION);
        $this->event->setResult(new ViewModel());

        $response = new Response();
        $this->event->setResponse($response);

        $this->listener->prepareExceptionViewModel($this->event);

        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * @covers       ::prepareExceptionViewModel
     * @dataProvider providerDefaultStatusCodes
     */
    public function testPrepareExceptionViewModelSetsStatusCodeWhenDefaultStatusCodeIsGiven($statusCode)
    {
        $exception = new Exception('Custom exception', $statusCode);
        $this->event->setParam('exception', $exception);

        $this->event->setError(Application::ERROR_EXCEPTION);
        $this->event->setResult(new ViewModel());


        $response = new Response();
        $this->event->setResponse($response);

        $this->listener->prepareExceptionViewModel($this->event);

        $this->assertSame($statusCode, $response->getStatusCode());
    }

    /**
     * @covers ::getDefaultStatusCodes
     */
    public function testCanGetDefaultStatusCodes()
    {
        $response      = new Response();
        $reasonPhrases = self::readAttribute($response, 'recommendedReasonPhrases');
        $expectedValue = array_keys($reasonPhrases);

        $method = new ReflectionMethod($this->listener, 'getDefaultStatusCodes');
        $method->setAccessible(true);

        $this->assertSame($expectedValue, $method->invoke($this->listener, $response));
    }

    /**
     * @covers ::getRenderer
     */
    public function testCanGetRenderer()
    {
        $method = new ReflectionMethod($this->listener, 'getRenderer');
        $method->setAccessible(true);

        $this->assertSame($this->renderer, $method->invoke($this->listener));
    }
}
