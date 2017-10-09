<?php

namespace FSth\Koa\Database;

class Db
{
    protected $sqlMap;

    public function __construct(SqlMap $sqlMap)
    {
        $this->sqlMap = $sqlMap;
    }

    public function query($sid, $data, $options = [])
    {
        $sqlMap = $this->sqlMap->getSql($sid, $data, $options);
        $connection = (yield MysqlPool::getInstance()->get());
        $execute = $sqlMap['execute'];
        $result = (yield $connection->$execute($sqlMap['sql']));
        MysqlPool::getInstance()->free($connection);
        yield $result;
    }
}