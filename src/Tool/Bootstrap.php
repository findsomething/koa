<?php

namespace FSth\Koa\Tool;

abstract class Bootstrap
{
    private $params;
    private $method;


    /**
     * @var
     *  app
     *      host
     *      port
     *      pid_file
     *  setting
     *      max_connection
     *      worker_num
     *      ...
     */
    private $config;

    public function __construct($params)
    {
        $this->params = $params;
        $this->validMethod = array('run', 'start', 'stop', 'restart');
    }

    public function handle()
    {
        $this->init();
        call_user_func_array(array($this, $this->method), array());
    }

    private function init()
    {
        if (count($this->params) != 3) {
            $this->method = "show";
            return;
        }
        $this->method = $this->params[1];
        if (!in_array($this->method, $this->validMethod)) {
            $this->method = "show";
            return;
        }

        $configFile = $this->params[2];
        if (!file_exists($configFile)) {
            $this->method = "show";
            return;
        }

        $this->config = include $configFile;
    }

    public function run()
    {
        $this->start(false);
    }

    public function start($daemon = true)
    {
        if (file_exists($this->config['app']['pid_file'])) {
            echo "server is already start\n";
            return;
        }
        echo "server is starting...\n";

        $this->startServer($daemon);
    }

    abstract function startServer($deamon);

    private function stop()
    {
        if (file_exists($this->config['app']['pid_file'])) {
            $pid = intval(file_get_contents($this->config['app']['pid_file']));
            if ($pid && posix_kill($pid, SIGTERM)) {
                while (1) {
                    if (file_exists($this->config['app']['pid_file'])) {
                        sleep(1);
                        continue;
                    }

                    echo "server has stopped.\n";
                    break;
                }
            }
        }
    }

    private function restart()
    {
        $this->stop();
        sleep(1);
        $this->start(true);
    }

    function show()
    {
        echo sprintf("usage:%s run|stop|start theServerConfigYourWantToLoad", $this->params[0]);
    }
}