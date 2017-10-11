<?php

namespace FSth\Koa\Database;

/**
 * Class SqlGenerator
 *  init/makeup setting for sql
 * @package FSth\Koa\Database
 */
class SqlGenerator
{
    const INSERT = 'insert';
    const SELECT = 'select';
    const UPDATE = 'update';
    const DELETE = 'delete';

    const INSERT_SQL = 'INSERT INTO %s #INSERT#';
    const INSERTS_SQL = 'INSERT INTO %s #INSERTS#';
    const SEARCH_SQL = 'SELECT * FROM %s WHERE #WHERE# #ORDER# #LIMIT#';
    const COUNT_SQL = 'SELECT count(*) FROM %s WHERE #WHERE#';
    const UPDATE_SQL = 'UPDATE %s SET #DATA# WHERE id = #{id} LIMIT 1';
    const DELETE_SQL = 'DELETE FROM %s WHERE id = #{id} LIMIT 1';
    const GET_SQL = 'SELECT * FROM %s WHERE id = #{id} LIMIT 1';

    const EXECUTE = 'executeQuery';
    const FETCH_ALL = 'fetchAll';
    const FETCH_COLUMN = 'fetchColumn';
    const FETCH_ASSOC = 'fetchAssoc';

    protected $config;

    /**
     * SqlGenerator constructor.
     * @param array $config
     *  $tableName => [
     *      'table' => $tableName,
     *      'search' => [
     *          'conditions' => [
     *              key1 => 'column1 = :key1'
     *              key2 => 'column2 >= :key2'
     *          ]
     *      ],
     *      ...
     *  ],
     *  $tableName1 => [
     *  ],
     *  ...
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function makeup()
    {
        $makeupResult = [];
        foreach ($this->config as $table => $extraTable) {
            $initTable = $this->initTable($table);
            $makeupResult[$table] = $this->merge($initTable, $extraTable);
        }
        return $makeupResult;
    }

    protected function merge($initTable, $extraTable)
    {
        foreach ($extraTable as $key => $value) {
            if (!empty($initTable[$key]) && is_array($initTable[$key]) && is_array($value)) {
                $initTable[$key] = array_merge($initTable[$key], $value);
            }
        }
        return $initTable;
    }

    protected function initTable($table)
    {
        return [
            'table' => $table,
            'insert' => $this->initInsert($table),
            'batch_insert' => $this->initBatchInsert($table),
            'search' => $this->initSearch($table),
            'count' => $this->initCount($table),
            'update' => $this->initUpdate($table),
            'delete' => $this->initDelete($table),
            'get' => $this->initGet($table)
        ];
    }

    protected function initGet($table)
    {
        return [
            'sql_type' => self::SELECT,
            'require' => [],
            'limit' => [],
            'sql' => sprintf(self::GET_SQL, $table),
            'execute' => self::FETCH_ASSOC
        ];
    }

    protected function initInsert($table)
    {
        return [
            'sql_type' => self::INSERT,
            'require' => [],
            'limit' => [],
            'sql' => sprintf(self::INSERT_SQL, $table),
            'execute' => self::EXECUTE
        ];
    }

    protected function initBatchInsert($table)
    {
        return [
            'sql_type' => self::INSERT,
            'require' => [],
            'limit' => [],
            'sql' => sprintf(self::INSERTS_SQL, $table),
            'execute' => self::EXECUTE
        ];
    }

    protected function initSearch($table)
    {
        return [
            'sql_type' => self::SELECT,
            'require' => [],
            'limit' => [],
            'conditions' => [],
            'sql' => sprintf(self::SEARCH_SQL, $table),
            'execute' => self::FETCH_ALL
        ];
    }

    protected function initCount($table)
    {
        return [
            'sql_type' => self::SELECT,
            'require' => [],
            'limit' => [],
            'conditions' => [],
            'sql' => sprintf(self::COUNT_SQL, $table),
            'execute' => self::FETCH_COLUMN
        ];
    }

    protected function initUpdate($table)
    {
        return [
            'sql_type' => self::UPDATE,
            'require' => ['id'],
            'sql' => sprintf(self::UPDATE_SQL, $table),
            'execute' => self::EXECUTE
        ];
    }

    protected function initDelete($table)
    {
        return [
            'sql_type' => self::DELETE,
            'require' => ['id'],
            'sql' => sprintf(self::DELETE_SQL, $table),
            'execute' => self::EXECUTE
        ];
    }
}