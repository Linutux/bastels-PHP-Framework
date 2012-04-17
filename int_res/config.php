<?php
class Config{
  public	$Site_Suffix = ".php";
  public	$Site_Praefix = "sites/";
  private	$MySQL_Passwort = "PASSWORD";
	private	$dbhost = "mysql02.bastelfreak.org";
	private	$dbuser = "USERNAME";
	private	$dbname = "framework";
	
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
}



?>