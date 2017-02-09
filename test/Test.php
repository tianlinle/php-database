<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/UserAccountModel.php';

use Tian\Database\ORM;
use Tian\Database\Test\UserAccountModel;

echo UserAccountModel::getTableName();
$pdo = new \PDO('mysql:dbname=project;host=112.74.43.17;charset=UTF8', 'root', '8ccf4fe40ect988dZ6Tioflk', [\PDO::ATTR_PERSISTENT => true]);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$orm = new ORM($pdo);
echo $orm->query(UserAccountModel::class)->getCreateTableSql();