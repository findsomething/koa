<?php

namespace FSth\Koa\Database;

use FSth\Koa\Singleton\Singleton;

class Db
{
    use Singleton;

    public function query($sid, $data, $options = [])
    {
        $sqlMap = SqlMap::getInstance()->getSql($sid, $data, $options);
        $connection = (yield MysqlPool::getInstance()->get());
        $execute = $sqlMap['execute'];
        $result = (yield $connection->$execute($sqlMap['sql']));
        MysqlPool::getInstance()->free($connection);
        yield $result;
    }
}