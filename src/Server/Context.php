<?php

namespace FSth\Koa\Server;

use FSth\Koa\Exception\KoaException;
use Monolog\Logger;

class Context
{
    /**
     * @var Application
     */
    public $app;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var Response
     */
    public $response;

    /**
     * @var \swoole_http_request
     */
    public $req;

    /**
     * @var \swoole_http_response
     */
    public $res;

    /**
     * @var Logger
     */
    public $logger;

    public $bodyType = 'text';

    public $state = [];
    public $respond = true;
    public $body;
    public $status;

    public $config;

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        /**
         * @var $fn callable
         */
        $fn = [$this->res, $name];
        return $fn(...$arguments);
    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
        return $this->request->$name;
    }

    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
        $this->response->$name = $value;
    }

    public function throwException($status, $message)
    {
        if ($message instanceof \Exception) {
            $ex = $message;
            throw new KoaException($status, $ex->getMessage(), $ex->getCode(), $ex->getPrevious());
        }
        throw new KoaException($status, $message);
    }
}