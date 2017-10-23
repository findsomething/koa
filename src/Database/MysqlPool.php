<?php

namespace FSth\Koa\Database;

use FSth\Koa\Database\NonSync\Client as AsyncClient;
use FSth\Koa\Database\NonSync\Proxy as AsyncProxy;
use FSth\DbProxy\Client as SyncClient;
use FSth\DbProxy\Proxy as SyncProxy;
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
     *  type async|sync
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
        if ($this->config['type'] == 'sync') {
            $client = new SyncClient($cfg['name'], $cfg['user'], $cfg['password'], $cfg['host'], $cfg['port']);
            $proxy = new SyncProxy($client);
            $proxy->setLogger($this->logger);
            return $proxy;
        }
        $client = new AsyncClient($cfg['name'], $cfg['user'], $cfg['password'], $cfg['host'], $cfg['port']);
        $proxy = new AsyncProxy($client);
        $proxy->setLogger($this->logger);
        return $proxy;
    }
}