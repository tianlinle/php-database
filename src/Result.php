<?php
namespace Tian\Database;

/**
* @author wangtianlin
*/
class Result
{
    const CONJUNCTION_AND = ' AND ';
    const CONJUNCTION_OR = ' OR ';

    const ORDER_DESC = ' DESC';
    const ORDER_ASC = ' ASC';

    protected $connection;
    protected $condiction = [];
    protected $order = [];
    protected $limit = '';
    protected $model;

    public function __construct($model, ORM $connection)
    {
        $this->model = $model;
        $this->connection = $connection;
    }

    public function where($column, $op, $value, $conjunction = self::CONJUNCTION_AND)
    {
        $condiction = $this->quoteColumn($column) . ' ' . $op . ' ' . $this->quoteValue($value);
        if (!empty($this->condiction)) {
            $condiction = $conjunction . $condiction;
        }
        $this->condiction[] = $condiction;
    }

    public function eq($column, $value, $conjunction = self::CONJUNCTION_AND)
    {
        $this->where($column, '=', $value, $conjunction);
        return $this;
    }

    public function gt($column, $value, $conjunction = self::CONJUNCTION_AND)
    {
        $this->where($column, '>', $value, $conjunction);
        return $this;
    }

    public function gte($column, $value, $conjunction = self::CONJUNCTION_AND)
    {
        $this->where($column, '>=', $value, $conjunction);
        return $this;
    }

    public function lt($column, $value, $conjunction = self::CONJUNCTION_AND)
    {
        $this->where($column, '<', $value, $conjunction);
        return $this;
    }

    public function lte($column, $value, $conjunction = self::CONJUNCTION_AND)
    {
        $this->where($column, '<=', $value, $conjunction);
        return $this;
    }

    public function neq($column, $value, $conjunction = self::CONJUNCTION_AND)
    {
        $this->where($column, '<>', $value, $conjunction);
        return $this;
    }

    public function like($column, $value, $conjunction = self::CONJUNCTION_AND)
    {
        $value = str_replace(['%', '_', '['], ['[%]', '[_]', '[[]'], $value);
        $this->where($column, 'LIKE', '%' . $value . '%', $conjunction);
        return $this;
    }

    public function in($column, $value, $conjunction = self::CONJUNCTION_AND)
    {
        if (is_string($column)) {
            $column = [$column];
            foreach ($value as &$val) {
                $val = [$val];
            }
            unset($val);
        }
        $columnArr = [];
        foreach ($column as $col) {
            $columnArr = self::quoteColumn($col);
        }
        $condiction .= '(' . implode(',', $columnArr) . ') IN ';
        $valueArr = [];
        foreach ($value as $val) {
            $innerVal = [];
            foreach ($val as $v) {
                $innerVal[] = self::quoteValue($v);
            }
            $valueArr[] = '(' . implode(',', $innerVal) . ')';
        }
        $condiction .= '(' . implode(',', $valueArr) . ')';
        if (!empty($this->condiction)) {
            $condiction = $conjunction . $condiction;
        }
        $this->condiction[] = $condiction;
        return $this;
    }

    public function order($field, $type = self::ORDER_ASC)
    {
        $this->order[] = self::quoteColumn($field) . $type;
    }

    public function limit($num, $offset = 0)
    {
        $this->limit = ' limit ' . intval($offset) . ',' . intval($num + $offset);
    }

    public function total()
    {
        $query = 'SELECT COUNT(*) FROM ' . $this->quoteColumn($this->model::getTableName());
        if (!empty($this->condiction)) {
            $query . ' WHERE ' . implode('', $this->condiction);
        }
        $statement = $this->connection->exec($query);
        return $statement->fetchColumn();
    }

    public function countCurrent()
    {

    }

    public function all()
    {
    }

    public function first()
    {

    }

    public function update()
    {

    }

    public function delete()
    {
        
    }

    public function exec($query)
    {

    }

    protected static function quoteValue($value)
    {
        return \PDO::quote($value);
    }

    protected static function quoteColumn($column)
    {
        return '`' . str_replace('`', '``', $column) . '`';
    }
}