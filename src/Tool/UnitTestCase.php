<?php

namespace FSth\Koa\Tool;

use FSth\Co\Call;

class UnitTestCase extends \PHPUnit_Framework_TestCase
{
    protected function runGo(callable $func)
    {
        Call::go(function () use ($func) {
            yield $this->clear();
            yield $func();
        }, function ($result, $e) {
            if ($e) {
                throw $e;
            }
        });
    }

    protected function clear()
    {

    }
}