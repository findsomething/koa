<?php

namespace FSth\Koa\Tool;

use FSth\Koa\Middleware\Router;
use FSth\Koa\Server\Context;

class LoadRoute
{
    private $routeConfig;

    private $router;

    /**
     * LoadRoute constructor.
     * @param $routeConfig
     *  controllerPath
     *  routeData
     *      [
     *          'Athena\\Log\\TestController' => [
     *              ['GET', '/v1/test_get/{id:\d+}', 'get']
     *          ]
     *      ]
     */
    public function __construct(array $routeConfig)
    {
        $this->routeConfig = $routeConfig;
        $this->router = new Router();
    }

    public function load()
    {
        foreach ($this->routeConfig as $class => $config) {
            $this->loadClass($class, $config);
        }
        return $this->router;
    }

    private function loadClass($class, array $config)
    {
        foreach ($config as $cfg) {
            list($httpMethod, $route, $method) = $cfg;
            $this->router->addRoute($httpMethod, $route, function (Context $ctx, $next, $vars) use ($class, $method) {
                $vars = is_array($vars) ? $vars : [];
                $class = new $class($ctx);
                $ctx->status = 200;
                $body = (yield call_user_func_array([$class,$method], $vars));
                $ctx->body = $body;
                yield;
            });
        }
    }
}