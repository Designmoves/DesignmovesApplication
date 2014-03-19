<?php
/**
 * Bugfix for issue #5965
 * @link https://github.com/zendframework/zf2/issues/5965
 */
namespace DesignmovesApplication\Mvc\Router\Http;

use Zend\Mvc\Router\Http\TranslatorAwareTreeRouteStack as BaseTranslatorAwareTreeRouteStack;

class TranslatorAwareTreeRouteStack extends BaseTranslatorAwareTreeRouteStack
{
    public function init()
    {
        parent::init();
        $this->routePluginManager->setInvokableClass('segment', __NAMESPACE__ . '\Segment');
    }
}
