<?php
// bashing everybody that trys to call this file directly 
$pathArray = explode("/", __FILE__);
if(!defined(strstr($pathArray[count($pathArray)-1], ".", true))){
	print_r($pathArray);
	echo "<br>wtf, wo ist das errorhandling?!<br>";
	// errorhandling can be placed here
	exit;
}

class Config{
	/* Start - We have to replace this */
  public	$Site_Suffix = ".php";
  public	$Site_Praefix = "sites/";
  private	$MySQL_Passwort = "ZKYWZdtWz2BcyN6a";
	private	$dbhost = "mysql02.bastelfreak.org";
	private	$dbuser = "framework";
	private	$dbname = "framework";
	/* End - We have to replace this */
	
	private $config = Array();
	private $dbSupport = Array();
	
	public function __construct(){
		$this->parseIniFile();
	}
	
	
	protected function get_MySQL_Passwort(){
		return $this->MySQL_Passwort;
	}
	
	protected function get_dbhost(){
		return $this->dbhost;
	}
	
	protected function get_dbuser(){
		return $this->dbuser;
	}
	
	protected function get_dbname(){
		return $this->dbname;
	}
	
	
	
	private function parseIniFile(){
		// get the init file
		$this->config = parse_ini_file(dirname(__FILE__)."/frameworkConfig.ini", true);
		/* Start - do we have support for different/any database? If so, write into $dbSupport */
		if(isset($this->config["MYSQL"])){
			$this->dbSupport["MYSQL"] = $this->config["MYSQL"];
		}
		if(isset($this->config["POSTGRESQL"])){
			$this->dbSupport["POSTGRESQL"] = $this->config["POSTGRESQL"];
		}
		/* End - do we have support for different/any database? If so, write into $dbSupport */
	}
	
	protected function getDbSupport(){
		return $this->dbSupport;
	}
}



?>