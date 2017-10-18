<?php

namespace FSth\Koa\Database\NonSync;

interface ClientEr
{
    public function connect();
    
    public function disconnect();
}