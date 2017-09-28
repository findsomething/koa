<?php

namespace FSth\Koa\Server;

use FSth\Koa\Singleton\Config;
use FSth\Koa\Exception\KoaException;
use FSth\Koa\HttpServer\HttpProtocol;
use FSth\Koa\HttpServer\HttpServer;
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
     * @param $fn
     *  middleware :: (Context $ctx, $next) -> void
     * @return $this
     */
    public function bind($fn)
    {
        $this->middleware[] = $fn;
        return $this;
    }

    public function listen(array $config = [], $daemon = false)
    {
        $this->config = $config;

        $this->fn = CallSolid::compose($this->middleware);

        $httpServer = $this->getHttpServer();
        if ($daemon) {
            $httpServer->daemonize();
        }

        $this->server = $httpServer->server;

        $protocol = $this->getHttpProtocol();

        $protocol->setMiddleWare($this->fn);
        $httpServer->setProtocol($protocol);

        $this->context->server = $this->server;

        $httpServer->listen();
    }

    protected function getHttpServer()
    {
        if (empty($serverClass = Config::getInstance()->get('http_server'))) {
            return new HttpServer($this->config);
        }
        $httpServer = new $serverClass($this->config);
        if ($httpServer instanceof HttpServer) {
            return $httpServer;
        }
        throw new KoaException("初始化httpServer失败");
    }

    protected function getHttpProtocol()
    {
        if (empty($protocolClass = Config::getInstance()->get('http_protocol'))) {
            return new HttpProtocol($this, $this->context);
        }
        $httpProtocol = new $protocolClass($this, $this->context);
        if ($httpProtocol instanceof HttpProtocol) {
            return $httpProtocol;
        }
        throw new KoaException("初始化httpProtocol失败");
    }
}