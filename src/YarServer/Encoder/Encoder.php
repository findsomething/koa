<?php
/**
 * from https://github.com/stcer/syar.git
 * thx
 */
namespace FSth\Koa\YarServer\Encoder;

interface Encoder
{
    function encode($message);
    function decode($message);
}