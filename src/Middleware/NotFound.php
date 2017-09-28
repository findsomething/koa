<?php

namespace FSth\Koa\Middleware;

use FSth\Koa\Server\Context;

class NotFound implements Middleware
{
    public function __invoke(Context $ctx, $next)
    {
        // TODO: Implement __invoke() method.
        yield $next;

        switch($ctx->status) {
            case 404:
                $ctx->body = "page not found";
                break;
            case 405:
                $ctx->body = "method not allowed";
                break;
        }
    }
}