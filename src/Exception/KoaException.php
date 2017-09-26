<?php

namespace FSth\Koa\Exception;

class KoaException extends \Exception
{
    public $expose = false;

    public $status;
}