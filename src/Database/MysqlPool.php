<?php

namespace FSth\Koa\Database;

use FSth\Koa\Database\NonSync\Client;
use FSth\Koa\Database\NonSync\Proxy;
use FSth\Koa\Singleton\Singleton;

class MysqlPool
{
    use Singleton;

    protected $config;
    protected $logger;

    protected $freeQueue;

    /**
     * @param $config
     *  server_info
     *      host
     *      port
     *      name
     *      user
     *      password
     *  max_conns
     * @param $logger
     */
    public function init(array $config, $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;

        $this->freeQueue = new \SplQueue();

        for ($i = 0; $i < $config['max_conns']; $i++) {
            $this->freeQueue->enqueue($this->createDb());
        }
    }

    public function get()
    {
        while (empty($db = $this->freeQueue->dequeue())) ;
        return $db;
    }

    public function free($db)
    {
        $this->freeQueue->enqueue($db);
    }

    protected function createDb()
    {
        $cfg = $this->config['server_info'];
        $client = new Client($cfg['name'], $cfg['user'], $cfg['password'], $cfg['host'], $cfg['port']);
        $proxy = new Proxy($client);
        $proxy->setLogger($this->logger);
        return $proxy;
    }
}