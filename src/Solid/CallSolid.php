<?php

namespace FSth\Koa\Solid;

use FSth\Koa\Server\Context;

class CallSolid
{
    public static function compose(array $middleware)
    {
        return function (Context $ctx = null) use ($middleware) {
            $ctx = $ctx ?: new Context();
            return self::array_right_reduce($middleware, function ($rightNext, $leftFn) use ($ctx) {
                return $leftFn($ctx, $rightNext);
            }, null);
        };
    }

    public static function array_right_reduce(array $input, callable $function, $initial = null)
    {
        return array_reduce(array_reverse($input, true), $function, $initial);
    }
}