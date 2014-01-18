<?php

namespace DesignmovesApplicationTest\Controller;

use DesignmovesApplication\Controller\IndexController;
use PHPUnit_Framework_TestCase;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

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

        // Check if ViewModel is standalone
        $this->assertTrue($result->terminate());

        // Test the parameters contained in the View model
        $variables = $result->getVariables();
        $this->assertCount(1, $variables);

        $this->assertSame($variables['content'], 'Placeholder page');
    }
}
