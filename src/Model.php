<?php
namespace Tian\Database;

/**
* @author wangtianlin
*/
class Model
{
    const TABLE_PREFIX = 'tb_';
    const COMMENT = '';
    const CLASS_NAME_EXT = 'Model';

    protected $row;

    protected static $columns = [];
    protected static $uk = [];
    protected static $pk = [];

    protected static function columns()
    {
        return [];
    }

    public function __construct($row = null)
    {
        if ($row) {
            $this->row = $row;
        } else {
            $this->row = [];
            $columns = static::getColumns();
            foreach ($columns as $column) {
                $value = null;
                if ($column->default !== null && !($column->default instanceof Literal)) {
                    $value = $column->default;
                }
                $this->row[$column->name] = $value;
            }
        }
    }

    public static function getColumns()
    {
        if (empty(static::$columns)) {
            static::$columns = static::columns();
            array_unshift(static::$columns, Column::int('id')->default(new Literal('PRIMARY KEY AUTO_INCREMENT')));
            array_push(static::$columns, Column::timestamp('created_time')->default(new Literal('DEFAULT CURRENT_TIMESTAMP')));
            array_push(static::$columns, Column::timestamp('updated_time')->default(new Literal('DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')));
        }
        return static::$columns;
    }

    public static function getTableName()
    {
        return static::TABLE_PREFIX . static::underscore(substr(static::class, 0, -strlen(static::CLASS_NAME_EXT)));
    }

    public static function camelize($string)
    {
        return strtr(ucwords(strtr($string, array('_' => ' ', '.' => '_ ', '\\' => '_ '))), array(' ' => ''));
    }

    public static function underscore($id)
    {
        return strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), str_replace('_', '.', $id)));
    }
}