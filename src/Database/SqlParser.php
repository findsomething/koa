<?php

namespace FSth\Koa\Database;

class SqlParser
{
    public static function parseWhere($conditions, $data = [])
    {
        $where = [];
        foreach ($data as $k => $v) {
            if (!empty($conditions[$k])) {
                list($item, $operation, $null) = explode(' ', $conditions[$k]);
                $where[] = [
                    $item, $operation, $v
                ];
            }
        }
        return $where;
    }
}