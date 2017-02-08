<?php
namespace Tian\Database\Test;

use Tian\Database\Model;
use Tian\Database\Column;

/**
* @author wangtianlin
*/
class UserAccountModel extends Model
{
    protected static $columns = [
        Column::char('name'),
        Column::char('password'),
        '1',
    ];
}