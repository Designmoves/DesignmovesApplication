<?php

namespace DesignmovesApplicationTest\Listener;

use Exception;
use DesignmovesApplication\Listener\ExceptionTemplateListener;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Zend\EventManager\EventManager;
use Zend\Http\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

/**
 * @coversDefaultClass DesignmovesApplication\Listener\ExceptionTemplateListener
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
        $this->event    = new MvcEvent;
        $this->renderer = new PhpRenderer;
        $this->listener = new ExceptionTemplateListener($this->renderer);
    }

    /**
     * @covers ::attach
     */
    public function testAttachesPrepareExceptionViewModelListenerOnEventDispatchError()
    {
        $eventManager = new EventManager;
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
        $eventManager = new EventManager;
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
        $this->listener->prepareExceptionViewModel($this->event);
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
        $this->event->setResult(new ViewModel);

        $response = new Response;
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
        $this->event->setResult(new ViewModel);


        $response = new Response;
        $this->event->setResponse($response);

        $this->listener->prepareExceptionViewModel($this->event);

        $this->assertSame($statusCode, $response->getStatusCode());
    }

    /**
     * @covers ::getDefaultStatusCodes
     */
    public function testCanGetDefaultStatusCodes()
    {
        $response = new Response;
        $property = new ReflectionProperty($response, 'recommendedReasonPhrases');
        $property->setAccessible(true);

        $reasonPhrases = $property->getValue($response);

        $method = new ReflectionMethod($this->listener, 'getDefaultStatusCodes');
        $method->setAccessible(true);

        $this->assertSame(array_keys($reasonPhrases), $method->invoke($this->listener, $response));
    }
}
