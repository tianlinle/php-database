<?php
namespace Tian\Database;

/**
* @author wangtianlin
*/
class Model
{
    const TABLE_PREFIX = 'tb_';
    const COMMENT = '';

    protected $row;

    protected static $columns = [];
    protected static $uniqueColumns = [];

    public function __construct($row = null)
    {
        $this->row = $row;
    }

    public static function getTableName()
    {
        return static::TABLE_PREFIX . static::underscore(static::class);
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