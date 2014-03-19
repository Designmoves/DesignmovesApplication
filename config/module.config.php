<?php

return array(
    'designmoves_application' => array(
        'force_lowercase_request' => true,
    ),

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
        /**
         * Bugfix for issue #5965
         * @link https://github.com/zendframework/zf2/issues/5965
         */
        'router_class' => 'DesignmovesApplication\Mvc\Router\Http\TranslatorAwareTreeRouteStack',
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
            'DesignmovesApplication\Listener\ExceptionTemplateListener' => 'DesignmovesApplication\Factory\Listener\ExceptionTemplateListenerFactory',
            'DesignmovesApplication\Options\ModuleOptions'              => 'DesignmovesApplication\Factory\Options\ModuleOptionsFactory',
            // Fix for issue 5978
            // @link https://github.com/zendframework/zf2/issues/5978
            'MvcTranslator'                                             => 'DesignmovesApplication\Mvc\Service\TranslatorServiceFactory',
            'Zend\Navigation\Service\DefaultNavigationFactory'          => 'Zend\Navigation\Service\DefaultNavigationFactory',
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
        'strategies' => array(
            'ViewJsonStrategy',
        ),
        'template_path_stack' => array(
            'designmoves_application' => __DIR__ . '/../view',
        ),
    ),
);
