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

class ExceptionTemplateListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Mock object
     * @var Zend\Mvc\MvcEvent
     */
    protected $eventMock;

    /**
     * Mock object
     * @var Zend\View\Renderer\PhpRenderer
     */
    protected $rendererMock;

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
        $this->eventMock = $this->getMockBuilder('Zend\Mvc\MvcEvent')
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->rendererMock = $this->getMockBuilder('Zend\View\Renderer\PhpRenderer')
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->listener = new ExceptionTemplateListener($this->rendererMock);

        $this->eventManager = new EventManager();
        $this->eventManager->attachAggregate($this->listener);
    }

    public function testCallbackExists()
    {
        $method       = 'prepareExceptionViewModel';
        $errorMessage = sprintf('Method with name "%s" does not exist', $method);
        $this->assertTrue(method_exists($this->listener, $method), $errorMessage);
    }

    public function testAttachedEventCount()
    {
        $this->assertCount(2, $this->eventManager->getEvents());
    }

    public function testAttachedEvents()
    {
        $events = array(
            MvcEvent::EVENT_DISPATCH_ERROR,
            MvcEvent::EVENT_RENDER_ERROR,
        );
        $this->assertSame($events, $this->eventManager->getEvents());
    }

    public function testAttachAggregateDispatchErrorEvent()
    {
        $eventName = MvcEvent::EVENT_DISPATCH_ERROR;
        $queue     = $this->eventManager->getListeners($eventName);
        $this->assertCount(1, $queue);

        $callbackHandlers = $queue->toArray();
        $this->assertCount(1, $callbackHandlers);

        $callbackHandler = $callbackHandlers[0];

        // Event name
        $this->assertSame($eventName, $callbackHandler->getMetaDatum('event'));

        // Callback
        $callback = $callbackHandler->getCallback();
        $this->assertInstanceOf('DesignmovesApplication\Listener\ExceptionTemplateListener', $callback[0]);
        $this->assertSame('prepareExceptionViewModel', $callback[1]);

        // Priority
        $this->assertSame(1, $callbackHandler->getMetaDatum('priority'));
    }

    public function testAttachAggregateRenderErrorEvent()
    {
        $eventName = MvcEvent::EVENT_RENDER_ERROR;
        $queue     = $this->eventManager->getListeners($eventName);
        $this->assertCount(1, $queue);

        $callbackHandlers = $queue->toArray();
        $this->assertCount(1, $callbackHandlers);

        $callbackHandler = $callbackHandlers[0];

        // Event name
        $this->assertSame($eventName, $callbackHandler->getMetaDatum('event'));

        // Callback
        $callback = $callbackHandler->getCallback();
        $this->assertInstanceOf('DesignmovesApplication\Listener\ExceptionTemplateListener', $callback[0]);
        $this->assertSame('prepareExceptionViewModel', $callback[1]);

        // Priority
        $this->assertSame(1, $callbackHandler->getMetaDatum('priority'));
    }

    public function testPrepareExceptionViewModelIgnoresWhenErrorIsEmpty()
    {
        $this->eventMock
             ->expects($this->once())
             ->method('getError')
             ->with()
             ->will($this->returnValue(null));

        $this->eventMock
             ->expects($this->never())
             ->method($this->equalTo('getResult'));

        $this->listener->prepareExceptionViewModel($this->eventMock);
    }

    /**
     * @dataProvider providerCustomStatusCodes
     */
    public function testPrepareExceptionViewModelSetsStatusCode500WhenCustomStatusCodeIsGiven($statusCode)
    {
        $this->eventMock
             ->expects($this->once())
             ->method('getError')
             ->with()
             ->will($this->returnValue(Application::ERROR_EXCEPTION));

        $viewModelMock = $this->getMockBuilder('Zend\View\Model\ViewModel')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->eventMock
             ->expects($this->once())
             ->method($this->equalTo('getResult'))
             ->will($this->returnValue($viewModelMock));

        $exception = new Exception('Custom exception', $statusCode);

        $this->eventMock
             ->expects($this->once())
             ->method($this->equalTo('getParam'))
             ->with($this->equalTo('exception'))
             ->will($this->returnValue($exception));

        $response = new Response();

        $this->eventMock
             ->expects($this->once())
             ->method($this->equalTo('getResponse'))
             ->with()
             ->will($this->returnValue($response));

        $this->listener->prepareExceptionViewModel($this->eventMock);

        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * @dataProvider providerDefaultStatusCodes
     */
    public function testPrepareExceptionViewModelSetsStatusCodeWhenDefaultStatusCodeIsGiven($statusCode)
    {
        $this->eventMock
             ->expects($this->once())
             ->method('getError')
             ->with()
             ->will($this->returnValue(Application::ERROR_EXCEPTION));

        $viewModelMock = $this->getMockBuilder('Zend\View\Model\ViewModel')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->eventMock
             ->expects($this->once())
             ->method($this->equalTo('getResult'))
             ->will($this->returnValue($viewModelMock));

        $exception = new Exception('Custom exception', $statusCode);

        $this->eventMock
             ->expects($this->once())
             ->method($this->equalTo('getParam'))
             ->with($this->equalTo('exception'))
             ->will($this->returnValue($exception));

        $response = new Response();

        $this->eventMock
             ->expects($this->once())
             ->method($this->equalTo('getResponse'))
             ->with()
             ->will($this->returnValue($response));

        $this->listener->prepareExceptionViewModel($this->eventMock);

        $this->assertSame($statusCode, $response->getStatusCode());
    }

    public function testGetDefaultStatusCodes()
    {
        $response = new Response;
        $property = new ReflectionProperty($response, 'recommendedReasonPhrases');
        $property->setAccessible(true);

        $reasonPhrases = $property->getValue($response);
        $expectedValue = array_keys($reasonPhrases);

        $method = new ReflectionMethod($this->listener, 'getDefaultStatusCodes');
        $method->setAccessible(true);

        $this->assertSame($expectedValue, $method->invoke($this->listener, $response));
    }
}
