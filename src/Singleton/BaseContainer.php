<?php

namespace FSth\Koa\Singleton;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;

class BaseContainer
{
    protected static $config = [];

    protected static $container;

    public function setValues(array $values)
    {
        static::$config = $values;
    }

    public function config($name, $default = null)
    {
        if (!isset(static::$config[$name])) {
            return $default;
        }

        return static::$config[$name];
    }

    public function setConfig($name, $value)
    {
        static::$config[$name] = $value;
    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
        return isset(static::$container[$name]) ? static::$container[$name] : null;
    }

    public function boot()
    {
        static::$container = new Container(static::$config);
        static::$container['logger'] = function ($container) {
            $logger = new Logger('run');
            $logger->pushHandler(new StreamHandler(static::$config['log_path'] . 'run.log'));
            return $logger;
        };
    }
}