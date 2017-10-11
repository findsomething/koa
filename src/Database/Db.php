<?php

namespace FSth\Koa\Database;

class Db
{
    protected $sqlMap;

    public function __construct(SqlMap $sqlMap)
    {
        $this->sqlMap = $sqlMap;
    }

    public function queryRaw($sql, $execute)
    {
        $connection = (yield MysqlPool::getInstance()->get());
        $result = (yield $connection->$execute($sql));
        MysqlPool::getInstance()->free($connection);
        yield $result;
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

    public function search($sid, $data = [], $orderBy = 'createdTime', $start = 0, $limit = 100)
    {
        $sqlMap = $this->sqlMap->getSqlMap($sid);
        $conditions = !empty($sqlMap['conditions']) ? $sqlMap['conditions'] : [];
        $where = SqlParser::parseWhere($conditions, $data);

        yield $this->query($sid, [
            'where' => $where,
            'order' => $orderBy,
            'limit' => sprintf("%d,%d", intval($start), intval($limit))
        ]);
    }

    public function count($sid, $data = [])
    {
        $sqlMap = $this->sqlMap->getSqlMap($sid);
        $conditions = !empty($sqlMap['conditions']) ? $sqlMap['conditions'] : [];
        $where = SqlParser::parseWhere($conditions, $data);

        yield $this->query($sid, [
            'where' => $where,
        ]);
    }

    public function insert($sid, $data)
    {
        yield $this->query($sid, [
            'insert' => $data
        ]);
    }

    public function batchInsert($sid, $datas)
    {
        yield $this->query($sid, [
            'inserts' => $datas
        ]);
    }

    public function update($sid, $id, $data)
    {
        yield $this->query($sid, [
            'data' => $data,
            'var' => [
                'id' => $id,
            ]
        ]);
    }

    public function delete($sid, $id)
    {
        yield $this->query($sid, [
            'var' => [
                'id' => $id,
            ]
        ]);
    }

    public function get($sid, $id)
    {
        yield $this->query($sid, [
            'var' => [
                'id' => $id
            ]
        ]);
    }
}