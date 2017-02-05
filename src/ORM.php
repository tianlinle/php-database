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
        return new Result($model, $this);
    }

    public function setDebug($callable)
    {
        $this->debug = $callable;
    }
}