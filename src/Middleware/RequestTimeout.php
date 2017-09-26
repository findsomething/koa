<?php

namespace FSth\Koa\Middleware;

use FSth\Co\Call;
use FSth\Koa\Exception\KoaException;
use FSth\Koa\Server\Context;

class RequestTimeout implements Middleware
{
    public $timeout;
    public $exception;

    private $timerId;

    public function __construct($timeout, \Exception $ex = null)
    {
        $this->timeout = $timeout;
        if ($ex === null) {
            $this->exception = new KoaException("Request timeout", 408);
        } else {
            $this->exception = $ex;
        }
    }

    public function __invoke(Context $ctx, $next)
    {
        // TODO: Implement __invoke() method.
        yield Call::race([
            Call::callCC(function ($k) {
                $this->timerId = swoole_timer_after($this->timeout, function () use ($k) {
                    $k(null, $this->exception);
                });
            }),
            function () use ($next) {
                yield $next;
                if (swoole_timer_exists($this->timerId)) {
                    swoole_timer_clear($this->timerId);
                }
            },
        ]);
    }
}