<?php

namespace FSth\Koa\YarServer;

use FSth\Koa\HttpServer\HttpProtocol;
use FSth\Koa\Server\Context;

class YarProtocol extends HttpProtocol
{
    protected function respond(Context $ctx)
    {
        if ($ctx->respond === false) return;

        $body = $ctx->body;
        $code = 200;

        if ($code !== null) {
            $ctx->res->status($code);
        }

        if ($body !== null) {
            $ctx->res->write($body);
        }

        $ctx->res->header("Content-Type", "application/octet-stream");

        $ctx->res->end();
    }
}