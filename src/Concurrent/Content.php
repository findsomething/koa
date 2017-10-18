<?php

namespace FSth\Koa\Concurrent;

use FSth\Co\Tool;

class Content
{
    const PENDING = 0;
    const FULFILLED = 1;
    const REJECTED = 2;

    public $state;
    public $value;
    public $exception;

    public function __construct()
    {
        $this->state = self::PENDING;
    }

    public function resolve($value)
    {
        $this->state = self::FULFILLED;
        $this->value = $value;
    }

    public function reject(\Exception $exception)
    {
        $this->state = self::REJECTED;
        $this->exception = $exception;
    }

    public function result()
    {
        while ($this->state == self::PENDING) {
            $result = (yield Tool::asyncSleep(1));
        }
        yield [$this->value, $this->exception];
    }
}