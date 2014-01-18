<?php

namespace DesignmovesApplication\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * @var bool
     */
    protected $forceLowercaseRequest;

    /**
     * @param bool $forceLowercaseRequest
     */
    public function setForceLowercaseRequest($forceLowercaseRequest)
    {
        $this->forceLowercaseRequest = (bool) $forceLowercaseRequest;
    }

    /**
     * @return bool
     */
    public function getForceLowercaseRequest()
    {
        return (bool) $this->forceLowercaseRequest;
    }
}
