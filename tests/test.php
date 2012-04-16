<?php
error_reporting(E_ALL);
  ini_set('error_reporting', E_ALL);
echo __FILE__."<br>";
echo strstr(__FILE__, "/")."<br>";

$pathArray = explode("/", __FILE__);
$pathArrayVar = count($pathArray)-1;
//$pathArray[$count($PathArray)-1];


print_r($pathArray[count($pathArray)-1]);
?>