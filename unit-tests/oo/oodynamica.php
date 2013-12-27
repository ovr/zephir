<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 28.12.13
 * Time: 1:24
 */

ini_set('display_errors', 1);
error_reporting(-1);

$class = new Test\Oo\OoDynamicA();
var_dump($class->getNew());