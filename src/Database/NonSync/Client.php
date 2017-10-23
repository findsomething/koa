<?php

namespace FSth\Koa\Database\NonSync;

use FSth\Koa\Concurrent\Content;
use FSth\Koa\Exception\DbException;

class Client implements ClientEr
{
    private $dbName;
    private $user;
    private $password;
    private $host;
    private $port;
    private $driver;
    private $charset;

    private $db;

    private $close;

    public function __construct($dbName, $user, $password, $host, $port, $driver = 'pdo_mysql', $charset = 'utf8')
    {
        $this->dbName = $dbName;
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
        $this->driver = $driver;
        $this->charset = $charset;

        $this->close = true;
    }

    public function getDb()
    {
        return $this->db;
    }

    public function connect()
    {
        // TODO: Implement connect() method.
        if ($this->valid()) {
            yield true;
            return;
        }

        $this->disconnect();
        $content = new Content();

        $this->db = new \swoole_mysql();

        $this->db->on('Close', function ($db) {
            $this->close = true;
            unset($this->db);
        });

        $this->db->connect($this->server(), function ($db, $r) use ($content) {
            if ($r === false) {
                $content->reject(new DbException("Connect MySql Failed [%d]:%s", $db->connect_errno, $db->connect_error));
            }
            $this->close = false;
            $content->resolve(true);
        });

        yield call_user_func([$this, 'result'], $content);
        return;
    }

    public function disconnect()
    {
        // TODO: Implement disconnect() method.
        try {
            if ($this->valid()) {
                $this->db->close();
            }
        } catch (\Exception $e) {

        } finally {
            $this->close = true;
            unset($this->db);
            $this->db = null;
        }
    }

    public function query($sql)
    {
        $content = new Content();
        try {
            if (!$this->valid()) {
                throw new DbException("MySql is invalid");
            }
            $this->db->query($sql, function ($db, $result) use ($content) {
                if ($result === false) {
                    $content->reject(new \Exception("执行失败" . $db->error));
                    return;
                }
                $content->resolve($result);
            });

        } catch (\Exception $e) {
            $content->reject($e);
        }
        yield call_user_func([$this, 'result'], $content);
    }

    public function executeQuery($sql)
    {
        yield $this->query($sql);
    }

    public function fetchAll($sql)
    {
        yield $this->query($sql);
    }

    public function fetchColumn($sql, $columnIndex = 0)
    {
        $result = (yield $this->fetchAssoc($sql));

        if (!is_array($result)) {
            yield $result;
            return;
        }

        $columnIndex = min($columnIndex, count($result));

        $item = array_pop($result);

        for ($i = 0; $i < $columnIndex; $i++) {
            $item = array_pop($result);
        }

        yield $item;
    }

    public function fetchAssoc($sql)
    {
        $result = (yield $this->query($sql));
        yield !empty($result[0]) ? $result[0] : $result;
    }

    public function exec($sql)
    {
        yield $this->query($sql);
    }

    public function valid()
    {
        return !empty($this->db) && !$this->close && ($this->db instanceof \swoole_mysql) ? true : false;
    }

    private function result(Content $content)
    {
        list ($value, $exception) = (yield $content->result());
        if ($exception) {
            throw $exception;
        }
        yield $value;
    }

    private function server()
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'user' => $this->user,
            'password' => $this->password,
            'database' => $this->dbName,
            'charset' => $this->charset,
        ];
    }
}