<?php

namespace FSth\Koa\Dao;

use FSth\Koa\Database\Db;

class BaseDao
{
    protected $table;
    protected $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function setDb(Db $db)
    {
        $this->db = $db;
    }

    public function insert($data)
    {
        $sid = $this->getSid(__FUNCTION__);
        yield $this->db->insert($sid, $data);
    }

    public function batchInsert($datas)
    {
        $sid = $this->getSid(__FUNCTION__);
        yield $this->db->batchInsert($sid, $datas);
    }

    public function update($id, $data)
    {
        $sid = $this->getSid(__FUNCTION__);
        yield $this->db->update($sid, $id, $data);
    }

    public function delete($id)
    {
        $sid = $this->getSid(__FUNCTION__);
        yield $this->db->delete($sid, $id);
    }

    public function search($data = [], $orderBy = 'createdTime', $start = 0, $limit = 100)
    {
        $sid = $this->getSid(__FUNCTION__);
        yield $this->db->search($sid, $data, $orderBy, $start, $limit);
    }

    public function count($data = [])
    {
        $sid = $this->getSid(__FUNCTION__);
        yield $this->db->count($sid, $data);
    }

    public function get($id)
    {
        $sid = $this->getSid(__FUNCTION__);
        yield $this->db->get($sid, $id);
    }

    protected function getSid($action)
    {
        $partName = '';
        switch (strtolower($action)) {
            case 'insert':
                $partName = 'insert';
                break;
            case 'batchinsert':
                $partName = 'batch_insert';
                break;
            case 'search':
                $partName = 'search';
                break;
            case 'count':
                $partName = 'count';
                break;
            case 'update':
                $partName = 'update';
                break;
            case 'delete':
                $partName = 'delete';
                break;
            case 'get':
                $partName = 'get';
                break;
        }
        return sprintf("%s.%s", $this->table, $partName);
    }
}