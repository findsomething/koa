<?php

namespace FSth\Koa\HttpServer;

class HttpServer
{
    public $server;

    protected $config;

    protected $host;
    protected $port;

    protected $protocol;

    protected $initBinds = array(
        'onServerStart' => 'ManagerStart',
        'onServerStop' => 'ManagerStop',
    );

    protected $binds = array(
        'onWorkerStart' => 'WorkerStart',
        'onRequest' => 'request'
    );

    protected $setting = [
        'max_connection' => 100,       //worker process num
        'worker_num' => 8,       //worker process num
        'max_request' => 10000,
        'backlog' => 128,        //listen backlog
        'open_tcp_keepalive' => 1,
        'heartbeat_check_interval' => 5,
        'heartbeat_idle_time' => 10,
        'http_parse_post' => false,
    ];

    /**
     * HttpServer constructor.
     * @param array $config
     *  bootstrap
     *  app array
     *      host
     *      port
     *      pid_file
     *  setting array
     *      settings for swooleServer...
     * @param callable
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $this->host = $this->config['app']['host'];
        $this->port = $this->config['app']['port'];

        $this->createServer();
    }

    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    public function listen()
    {
        $this->init();
        $this->registerSwooleEvent();
        $this->server->start();
    }

    public function onServerStart($serv)
    {
        if (!empty($this->setting['daemonize']) && !empty($this->config['app']['pid_file'])) {
            file_put_contents($this->config['app']['pid_file'], $serv->master_pid);
        }
    }

    public function onServerStop($serv)
    {
        if (!empty($this->config['app']['pid_file']) && file_exists($this->config['app']['pid_file'])) {
            unlink($this->config['app']['pid_file']);
        }
    }

    protected function init()
    {

    }

    protected function createServer()
    {
        $this->server = new \swoole_http_server($this->host, $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $this->server->setting($this->getSetting());
    }

    protected function registerSwooleEvent()
    {
        foreach ($this->initBinds as $method => $evt) {
            $this->server->on($evt, array($this, $method));
        }

        foreach ($this->binds as $method => $evt) {
            if (method_exists($this->protocol, $method)) {
                $this->server->on($evt, array($this->protocol, $method));
            }
        }
    }

    protected function getSetting()
    {
        return ['host' => $this->host, 'port' => $this->port] + $this->setting + $this->config['setting'];
    }
}