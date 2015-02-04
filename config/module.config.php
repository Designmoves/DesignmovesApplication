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

return array(
    'controllers' => array(
        'invokables' => array(
            'DesignmovesApplication\Controller\Index' => 'DesignmovesApplication\Controller\IndexController',
        ),
    ),

    'navigation' => array(
        'default' => array(
        ),
    ),

    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        '__NAMESPACE__' => 'DesignmovesApplication\Controller',
                        'controller'    => 'Index',
                        'action'        => 'placeholder',
                    ),
                ),
                'may_terminate' => true,
                // Make sure this is the last route to test against
                'priority' => -1000,
            ),
        ),
    ),

    'service_manager' => array(
        'aliases' => array(
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'translator' => 'MvcTranslator',
        ),
        'factories' => array(
            'DesignmovesApplication\Options\ModuleOptions' =>
                'DesignmovesApplication\Factory\Options\ModuleOptionsFactory',
            'DesignmovesApplication\View\Strategy\ExceptionStrategy' =>
                'DesignmovesApplication\Factory\View\Strategy\ExceptionStrategyFactory',
            'Zend\Navigation\Service\DefaultNavigationFactory' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        ),
    ),

    'translator' => array(
        'locale' => 'nl_NL',
        'translation_file_patterns' => array(
            array(
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => 'lang.array.%s.php',
            ),
        ),
    ),

    'view_manager' => array(
        'display_exceptions'       => true,
        'display_not_found_reason' => true,
        'doctype'                  => 'HTML5',
        'exception_template'       => 'error/index',
        'mvc_strategies' => array(
            'DesignmovesApplication\View\Strategy\ExceptionStrategy',
        ),
        'not_found_template'       => 'error/404',
        'strategies' => array(
            'ViewJsonStrategy',
        ),
        'template_map' => array(
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'error/partial/exception' => __DIR__ . '/../view/error/partial/exception.phtml',
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'template_path_stack' => array(
            'designmoves_application' => __DIR__ . '/../view',
        ),
    ),
);
