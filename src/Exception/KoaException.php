<?php

namespace FSth\Koa;

class KoaException extends \Exception
{
    public $expose = false;

    public $status;
}