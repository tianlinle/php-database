<?php
namespace Tian\Database;

/**
* @author wangtianlin
*/
class Query
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
        $condiction = self::quoteColumn($column) . ' ' . $op . ' ' . self::quoteValue($value);
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
        $sql = 'SELECT COUNT(*) FROM ' . self::quoteColumn($this->model::getTableName());
        $sql = $this->appendWherePart($sql);
        $statement = $this->connection->exec($sql);
        return $statement->fetchColumn();
    }

    public function all()
    {
        $sql = $this->selectSql();
        $statement = $this->connection->exec($sql);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $collection = new Collection();
        $class = $this->model;
        foreach ($rows as $row) {
            $model = new $class($row);
            $collection[] = $model;
        }
        return $collection;
    }

    public function first()
    {
        $sql = $this->selectSql();
        $statement = $this->connection->exec($sql);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        $model = null;
        if ($row) {
            $class = $this->model;
            $model = new $class($row);
        }
        return $model;
    }

    public function insert($map)
    {
        $sql = 'INSERT INTO ' . self::quoteColumn($this->model::getTableName()) . ' ';
        $columns = [];
        $values = [];
        foreach ($map as $k => $v) {
            $columns[] = self::quoteColumn($k);
            $values[] = self::quoteValue($v);
        }
        $sql .= '(' . implode(' , ', $columns) . ') VALUES (' . implode(' , ', $values) . ')';
        $statement = $this->connection->exec($sql);
        return $statement->rowCount();
    }

    public function update($values)
    {
        $sql = 'UPDATE ' . self::quoteColumn($this->model::getTableName()) . ' SET ';
        $arr = [];
        foreach ($values as $column => $value) {
            $arr[] = self::quoteColumn($column) . ' = ' . self::quoteValue($value);
        }
        $sql .= implode(' , ', $arr);
        $sql = $this->appendWherePart($sql);
        $statement = $this->connection->exec($sql);
        return $statement->rowCount();
    }

    public function delete()
    {
        $sql = 'DELETE FROM ' . self::quoteColumn($this->model::getTableName());
        $sql = $this->appendWherePart($sql);
        $statement = $this->connection->exec($sql);
        return $statement->rowCount();
    }

    public function getCreateTableSql()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . self::quoteColumn($this->model::getTableName());
        $columns = [];
        $columns[] = '`id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT';
        foreach ($this->model::$columns as $col) {
            $type = $col->type;
            if ($col->length) {
                $type .= '(' . $col->length . ')';
            }
            $column = self::quoteColumn($col->name) . ' ' . $type;
            if (!$col->nullable) {
                $column .= ' NOT NULL';
            }
            if ($col->default !== null) {
                $column .= ' DEFAULT ' . self::quoteValue($col->default);
            }
            if ($col->comment != '') {
                $column .= ' COMMENT ' . self::quoteValue($col->comment);
            }
            $columns[] = $column;
        }
        foreach ($this->model::$uniqueColumns as $v) {
            if (is_string($v)) {
                $v = [$v];
            }
            $cs = [];
            foreach ($v as $o) {
                $cs[] = self::quoteColumn($o);
            }
            $columns[] = 'UNIQUE (' . implode(',', $cs) . ')';
        }
        $sql .= '(' . implode(',', $columns) . ')' . 'ENGINE=InnoDB CHARACTER SET utf8mb4';
        if ($this->model::$comment) {
            $sql .= ' COMMENT ' . self::quoteValue($this->model::$comment);
        }
        return $sql;
    }

    protected function selectSql()
    {
        $sql = 'SELECT * FROM ' . self::quoteColumn($this->model::getTableName());
        $sql = $this->appendWherePart($sql);
        if (!empty($this->order)) {
            $sql .= ' ORDER BY ' . implode(',', $this->order);
        }
        if ($this->limit) {
            $sql .= $this->limit;
        }
        return $sql;
    }

    protected function appendWherePart($sql)
    {
        if (!empty($this->condiction)) {
            $sql .= ' WHERE ' . implode('', $this->condiction);
        }
        return $sql;
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