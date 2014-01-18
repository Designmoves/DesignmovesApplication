<?php

namespace DesignmovesApplication\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    /**
     * Default action
     *
     * @return ViewModel
     */
    public function placeholderAction()
    {
        $viewModel = parent::indexAction();
        $viewModel->setTerminal(true);
        return $viewModel;
    }
}
