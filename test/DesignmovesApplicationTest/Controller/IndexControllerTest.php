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

namespace DesignmovesApplicationTest\Controller;

use DesignmovesApplication\Controller\IndexController;
use PHPUnit_Framework_TestCase;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

/**
 * @coversDefaultClass DesignmovesApplication\Controller\IndexController
 */
class IndexControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var IndexController
     */
    protected $controller;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var RouteMatch
     */
    protected $routeMatch;

    /**
     * @var MvcEvent
     */
    protected $event;

    public function setUp()
    {
        $this->controller = new IndexController();
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array());
        $this->event      = new MvcEvent();
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
    }

    public function test404()
    {
        $this->routeMatch->setParam('action', 'action-that-doesnt-exist');
        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @covers ::placeholderAction
     */
    public function testPlaceholderAction()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'placeholder');

        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);

        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);

        // Check if ViewModel is not standalone
        $this->assertFalse($result->terminate());

        // Test the parameters contained in the View model
        $variables = $result->getVariables();
        $this->assertCount(1, $variables);

        $this->assertSame($variables['content'], 'Placeholder page');
    }
}
