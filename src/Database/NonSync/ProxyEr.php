<?php

namespace FSth\Koa\Database\NonSync;

interface ProxyEr
{
    public function setLogger($logger);

    public function __call($method, $args);
}