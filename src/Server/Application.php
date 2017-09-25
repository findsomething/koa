<?php

namespace FSth\Koa\Server;

use FSth\Co\Call;
use FSth\Co\Tool;
use FSth\Koa\HttpServer\HttpProtocol;
use FSth\Koa\HttpServer\HttpServer;
use FSth\Koa\Middleware\LoggerHandler;
use FSth\Koa\Middleware\Middleware;
use FSth\Koa\Solid\CallSolid;

class Application
{
    /**
     * @var \swoole_http_server
     */
    public $server;

    /**
     * @var Context
     */
    protected $context;

    protected $middleware = [];

    protected $fn;

    protected $config;

    protected $protocol;

    public function __construct()
    {
        $this->context = new Context();
        $this->context->app = $this;
    }

    /**
     * @param Middleware $fn
     *  middleware :: (Context $ctx, $next) -> void
     * @return $this
     */
    public function bind(Middleware $fn)
    {
        $this->middleware[] = $fn;
        return $this;
    }

    public function listen(array $config = [])
    {
        $this->fn = CallSolid::compose($this->middleware);

        $httpServer = new HttpServer($config);
        $this->server = $httpServer->server;

        $protocol = new HttpProtocol($this, $this->context);
        $protocol->setMiddleWare($this->fn);
        $httpServer->setProtocol($protocol);

        $this->context->server = $this->server;

        $httpServer->listen();

    }
}