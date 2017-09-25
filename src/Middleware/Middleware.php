<?php

namespace FSth\Koa\Middleware;

use FSth\Koa\Server\Context;

interface Middleware
{
    public function __invoke(Context $ctx, $next);
}