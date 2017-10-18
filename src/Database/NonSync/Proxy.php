<?php

namespace FSth\Koa\Database\NonSync;

class Proxy implements ProxyEr
{
    protected $maxReconnectTimes = 3;
    protected $storage;
    protected $logger;
    protected $sleep = true;
    protected $sleepTime = 1;

    public function __construct(Client $client)
    {
        $this->storage = $client;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function __call($method, $args)
    {
        // TODO: Implement __call() method.
        try {
            $result = (yield $this->storage->connect());
            yield (call_user_func_array([$this->storage, $method], $args));
            return;
        } catch (\Exception $e) {
            $this->logger->error("db execute error", [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'method' => $method,
                'args' => $args
            ]);
            throw $e;
        }
    }

}