<?php
namespace Tian\Database;

/**
* @author wangtianlin
*/
class Column
{
    const TYPE_CHAR = 'CHAR';
    const TYPE_VARCHAR = 'VARCHAR';
    const TYPE_INT = 'INT';
    const TYPE_TIMESTAMP = 'TIMESTAMP';

    public $name;
    public $type;
    public $length;
    public $nullable = true;
    public $default = null;
    public $comment = '';

    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public static function char($name, $length = 255)
    {
        $column = new static($name, self::TYPE_CHAR);
        $column->length = $length;
        return $column;
    }

    public static function varchar($name, $length = 65535)
    {
        $column = new static($name, self::TYPE_VARCHAR);
        $column->length = $length;
        return $column;
    }

    public static function int($name, $length = 11)
    {
        $column = new static($name, self::TYPE_INT);
        $column->length = $length;
        return $column;
    }

    public static function timestamp($name)
    {
        return new static($name, self::TYPE_TIMESTAMP);
    }

    public function notNull()
    {
        $this->nullable = false;
        return this;
    }

    public function default($value)
    {
        $this->default = $value;
        return this;
    }

    public function comment($string)
    {
        $this->comment = $string;
        return this;
    }
}