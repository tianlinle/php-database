<?php
namespace Tian\Database;

/**
* @author wangtianlin
*/
class ORM
{
    protected $connection;
    protected $debug;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function query($model)
    {
        return new Query($model, $this);
    }

    public function setDebug($callable)
    {
        $this->debug = $callable;
    }

    public function exec($query, $params = [])
    {
        $statement = $connection->prepare($query);
        $statement->execute($params);
        return $statement;
    }
}