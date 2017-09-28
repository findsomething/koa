<?php

namespace FSth\Koa\Middleware;

use FSth\Koa\Server\Context;

class BodyDetector implements Middleware
{
    public function __invoke(Context $ctx, $next)
    {
        // TODO: Implement __invoke() method.
        yield $next;
        $bodyFormat = isset($ctx->bodyType) ? $ctx->bodyType : "text";
        switch ($bodyFormat) {
            case "json" :
                $body = (isset($ctx->body) && is_array($ctx->body)) ? $ctx->body : [];
                $ctx->body = json_encode($body);
                break;
            case "text":
            default:
                break;
        }
    }
}