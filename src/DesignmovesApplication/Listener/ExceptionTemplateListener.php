<?php

namespace DesignmovesApplication\Listener;

use ReflectionClass;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface;
use Zend\View\Renderer\PhpRenderer;

class ExceptionTemplateListener implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @var PhpRenderer
     */
    protected $renderer;

    /**
     * Constructor
     *
     * @param PhpRenderer $renderer
     */
    public function __construct(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Attach events
     *
     * @param EventManagerInterface $eventManager
     */
    public function attach(EventManagerInterface $eventManager)
    {
        $callback = array($this, 'prepareExceptionViewModel');

        $this->listeners[] = $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, $callback);
        $this->listeners[] = $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR  , $callback);
    }

    /**
     * Detach events
     *
     * @param EventManagerInterface $eventManager
     */
    public function detach(EventManagerInterface $eventManager)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($eventManager->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Handle thrown exceptions.
     * Set message and template for the different exception error codes.
     *
     * <code>
     * 404 error: Zend\Mvc\View\Http\RouteNotFoundStrategy<br />
     * 500 error: Zend\Mvc\View\Http\ExceptionStrategy
     * </code>
     *
     * @link http://zend-framework-community.634137.n4.nabble.com/Throw-exception-and-have-a-Status-404-Not-Found-response-tp4655198p4655263.html
     * @todo Look into implementation with <code>RouteNotFoundStrategy</code> and <code>ExceptionStrategy</code>
     *
     * @param EventInterface $event
     */
    public function prepareExceptionViewModel(EventInterface $event)
    {
        // Do nothing if no error in the event
        $error = $event->getError();
        if (empty($error)) {
            return;
        }

        // Do nothing if the error is not an exception
        if (Application::ERROR_EXCEPTION != $error) {
            return;
        }

        // Do nothing if the result is a response object
        $viewModel = $event->getResult();
        if ($viewModel instanceof ResponseInterface) {
            return;
        }

        // Do nothing if the exception can not be found
        $exception = $event->getParam('exception', false);
        if (!$exception instanceof \Exception) {
            return;
        }

        $response           = $event->getResponse();
        $defaultStatusCodes = $this->getDefaultStatusCodes($response);
        $statusCode         = $exception->getCode();

        if (!in_array($statusCode, $defaultStatusCodes)) {
            $statusCode = 500;
        }

        $response->setStatusCode($statusCode);

        $template = 'error/' . $statusCode;
        if ($this->renderer->resolver($template)) {
            $viewModel->setTemplate($template);
        }
    }

    /**
     * @param  Response $response
     * @return array
     */
    protected function getDefaultStatusCodes(Response $response)
    {
        $reflection = new ReflectionClass($response);

        $propertyName = 'recommendedReasonPhrases';
        if (!$reflection->hasProperty($propertyName)) {
            throw new Exception\LogicException(sprintf(
                'Property with name "%s" does not exist in %s',
                $propertyName,
                is_object($response) ? get_class($response) : gettype($response)
            ));
        }

        $reflectionProperty = $reflection->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $propertyValue = $reflectionProperty->getValue($response);

        return array_keys($propertyValue);
    }
}
