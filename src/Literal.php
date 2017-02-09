<?php
namespace Tian\Database;

/**
* @author wangtianlin
*/
class Literal
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}