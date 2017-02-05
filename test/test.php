<?php
$arr = ['a', 'b'];
foreach ($arr as &$v) {
    $v = [$v];
}
var_dump($arr);