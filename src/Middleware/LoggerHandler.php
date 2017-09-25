<?php

namespace FSth\Koa\Middleware;

use FSth\Koa\Server\Context;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerHandler implements Middleware
{
    public function __invoke(Context $ctx, $next)
    {
        // TODO: Implement __invoke() method.
        $logPath = $ctx->config['log']['path'];
        $mode = $ctx->config['log']['mode'];
        $name = !empty($ctx->config['log']['name']) ? $ctx->config['log']['name'] : "run";
        $ctx->logger = $this->createLogger($name, $logPath, $mode);
        
        yield $next;
    }

    protected function createLogger($name, $logPath, $mode)
    {
        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler($logPath . $name . ".log", $mode));
        return $logger;
    }
}