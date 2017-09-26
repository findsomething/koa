<?php

namespace FSth\Koa\Middleware;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use FSth\Koa\Server\Context;

class Router extends RouteCollector
{
    public $dispatcher;

    public function __construct()
    {
        $routerParser = new Std();
        $dataGenerator = new GroupCountBased();
        parent::__construct($routerParser, $dataGenerator);
    }

    public function routes()
    {
        $this->dispatcher = new Dispatcher\GroupCountBased($this->getData());
        return [$this, "dispatch"];
    }

    public function dispatch(Context $ctx, $next)
    {
        if ($this->dispatcher === null) {
            $this->routes();
        }

        $uri = $ctx->url;

        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $this->dispatcher->dispatch(strtoupper($ctx->method), $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $ctx->status = 404;
                yield $next;
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $ctx->status = 405;
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                yield $handler($ctx, $next, $vars);
                break;
        }
    }
}