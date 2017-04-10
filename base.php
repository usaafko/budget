<?php
/**
 * Created by PhpStorm.
 * User: ilya
 * Date: 10.04.2017
 * Time: 13:43
 */
$db = mysql_connect("localhost", "budget", "I4o8I9d7");
mysql_select_db("budget", $db);
mysql_query("set names utf8", $db);
if (!isset($login)) $login = '';
