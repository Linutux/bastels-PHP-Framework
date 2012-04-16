<?php
// This is the bootstrap file for my framework

// define some paths
// path to the framework file
define("frPath", "int_res");
//complete path incl / to the framework
define("rootPathFr", dirname(__FILE__)."/");
// path to 

// prevent direct calls at any core file
// every core file needs something like this:
// bashing everybody that trys to call this file directly 
//$pathArray = explode("/", __FILE__);
//if(!defined(strstr($pathArray[count($pathArray)-1], ".", true))){
//	print_r($pathArray);
//	echo "<br>wtf, wo ist das errorhandling?!<br>";
//	// errorhandling can be placed here
//	exit;
//}

if(file_exists(rootPathFr."usermanagement.php")){
	define("usermanagement", "usermanagement.php");
}else{
// errorhandler here
}
if(file_exists(rootPathFr."basement.php")){
	define("basement", "basement.php");
}else{
// errorhandler here
}
if(file_exists(rootPathFr."config.php")){
	define("config", "config.php");
}else{
// errorhandler here
}
if(file_exists(rootPathFr."bootstrap.php")){
	define("bootstrap", "bootstrap.php");
}else{
// errorhandler here
}
if(file_exists(rootPathFr."templateEngine.php")){
	define("templateEngine", "templateEngine.php");
}else{
// errorhandler here
}
if(file_exists(rootPathFr."database.php")){
	define("database", "database.php");
}else{
// errorhandler here
}
if(file_exists(rootPathFr."database.mysql.php")){
	define("mysql", "database.mysql.php");
}else{
// errorhandler here
}
require(rootPathFr.config);
require(rootPathFr.basement);
require(rootPathFr.database);
require(rootPathFr.mysql);
//set_error_handler(array("basement", "errorHandler"));
?>