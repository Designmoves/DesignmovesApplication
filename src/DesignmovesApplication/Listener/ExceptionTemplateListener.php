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

namespace DesignmovesApplication\Listener;

use Exception;
use ReflectionProperty;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface;
use Zend\View\Renderer\PhpRenderer;

class ExceptionTemplateListener extends AbstractListenerAggregate
{
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
        $this->listeners[] = $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, $callback);
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
        if (!$exception instanceof Exception) {
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
        if ($this->getRenderer()->resolver($template)) {
            $viewModel->setTemplate($template);
        }
    }

    /**
     * @param  Response $response
     * @return array
     */
    protected function getDefaultStatusCodes(Response $response)
    {
        $property = new ReflectionProperty($response, 'recommendedReasonPhrases');
        $property->setAccessible(true);

        $value = $property->getValue($response);

        return array_keys($value);
    }

    /**
     * @return PhpRenderer
     */
    protected function getRenderer()
    {
        return $this->renderer;
    }
}
