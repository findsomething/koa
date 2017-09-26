<?php

namespace FSth\Koa\Singleton;

class Config
{
    use Singleton;

    private $config;

    public function loadConfig($config)
    {
        $this->config = $config;
    }

    public function get($key, $key2 = null)
    {
        $config = isset($this->config[$key]) ? $this->config[$key] : null;
        if (!empty($key2) && is_array($config) && isset($config[$key2])) {
            return $config[$key2];
        }
        return $config;
    }
}