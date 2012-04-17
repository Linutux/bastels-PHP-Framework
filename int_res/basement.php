<?php
// bashing everybody that trys to call this file directly 
if (!defined('IN_FRAMEWORK')){
	exit;
}
// require "ext_res/cerberus-filterclass/input-filter.php";

class basement extends Config{
	
	private $db;
	public function __construct(){
	parent::__construct();
	}
	public function db_connect(){
		$this->db = new MySQLi(parent::get_dbhost(), parent::get_dbuser(), parent::get_MySQL_Passwort(), parent::get_dbname());
		if (mysqli_connect_errno() == 0){
			$this->db->Query("SET 
							character_set_results = 'utf-8', 
							character_set_client = 'utf-8', 
							character_set_connection = 'utf-8', 
							character_set_database = 'utf-8', 
							character_set_server = 'utf-8'
						"); 
			return $this->db;
		}
		else{
			return false;
		}
	}
	
	public function getMySQL(){
		return $this->db;
	}
	
	
	protected function CleanGet($clean = ""){
		if(!empty($clean)){
			$clean = trim(strip_tags($clean));
			return $clean;
		}
	}
	
	public function errorHandler($errorCode, $errorMessage, $file, $line){
		$sql = "INSERT INTO log_error (	`type`,
																	`description`,
																	`file`,
																	`line`,
																	`date`
																) 
																VALUES (	'".$errorCode."',
																					'".addslashes($errorMessage)."',
																					'".$file."',
																					'".$line."',
																					now()
																				)";
		$Sql = $this->db_connect();
		$Sql->Query($sql);
		return TRUE;
	}
}







?>
