<?php

namespace FSth\Koa\Middleware;

use FSth\Koa\Exception\KoaException;
use FSth\Koa\Server\Context;

class ExceptionHandler implements Middleware
{
    public function __invoke(Context $ctx, $next)
    {
        // TODO: Implement __invoke() method.
        try {
            yield $next;
        } catch (\Exception $ex) {
            $status = 500;
            $code = $ex->getCode() ?: 0;
            $msg = "Internal Error";

            if ($ex instanceof KoaException) {
                $status = $ex->status;
                if ($ex->expose) {
                    $msg = $ex->getMessage();
                }
            }

            $err = ['code' => $code, 'msg' => $msg];
            if ($ctx->accept("json")) {
                $ctx->status = 200;
                $ctx->body = $err;
            } else {
                $ctx->status = $status;
                $ctx->body = $err;
            }
        }
    }
}