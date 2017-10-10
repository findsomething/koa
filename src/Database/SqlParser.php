<?php

namespace FSth\Koa\Database;

class SqlParser
{
    public static function parseWhere($conditions, $data = [])
    {
        $where = [];
        if (!is_array($conditions) || !is_array($data)) {
            return $where;
        }
        foreach ($data as $k => $v) {
            if (!empty($conditions[$k]) && strlen(trim($v)) > 0) {
                list($item, $operation, $null) = explode(' ', $conditions[$k]);
                $where[] = [
                    $item, $operation, $v
                ];
            }
        }
        return $where;
    }
}