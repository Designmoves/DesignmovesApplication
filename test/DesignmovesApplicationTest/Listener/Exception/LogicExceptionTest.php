<?php

namespace DesignmovesApplicationTest\Listener\Exception;

use DesignmovesApplication\Listener\Exception\LogicException;
use PHPUnit_Framework_TestCase;

class LogicExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var LogicException
     */
    protected $exception;

    public function setUp()
    {
        $this->exception = new LogicException();
    }

    public function testImplementsExceptionInterface()
    {
        $this->assertInstanceOf('DesignmovesApplication\Exception\ExceptionInterface', $this->exception);
    }

    public function testImplementsListenerExceptionInterface()
    {
        $this->assertInstanceOf('DesignmovesApplication\Listener\Exception\ExceptionInterface', $this->exception);
    }
}
