<?php
 static class mysql extends basement implements database{
//class mysql extends basement implements database{
	//private static $instance;
	private $link;
	private $querys = Array();
	
	public function __construct(){
		parent::__construct();
		$this->connect2Database();
	}
	
//	public function __clone(){
//        trigger_error('Clonen ist nicht erlaubt.', E_USER_ERROR);
//    }

//    public function __wakeup(){
//        trigger_error('Deserialisierung ist nicht erlaubt.', E_USER_ERROR);
//    }
	
	public function connect2Database(){
		$vars = $this->getDbSupport();
		$this->link = new MySQLi($vars["MYSQL"]["MYSQLserver"], $vars["MYSQL"]["MYSQLuser"], $vars["MYSQL"]["MYSQLpasword"], $vars["MYSQL"]["MYSQLdatabase"]);
		if ($this->link->connect_errno){
			trigger_error("Problem with establishing a database connection", $this->link->connect_error);
			exit();
		}
		$this->doQuery("SET 
							character_set_results = 'utf8', 
							character_set_client = 'utf8', 
							character_set_connection = 'utf8', 
							character_set_database = 'utf8', 
							character_set_server = 'utf8'
						"); 
		unset($vars);
	}
	
	public function doQuery($query){
		$query = trim($query);
		//echo "<pre>".$query."</pre>";
		//$query = $this->link->real_escape_string($query); // why does it not work? dafuq?
		//echo "<pre>".$query."</pre>";
		$this->querys[] = $query;
		$this->link->query($query);
		if($this->link->errno){
			trigger_error($this->link->error, E_USER_WARNING);
			echo "<pre>".$query."</pre>";
			return FALSE;
		}else{
			return TRUE;
		}
		
	}
}









?>