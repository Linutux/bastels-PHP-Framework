<?php
// bashing everybody that trys to call this file directly 
$pathArray = explode("/", __FILE__);
if(!defined(strstr($pathArray[count($pathArray)-1], ".", true))){
	print_r($pathArray);
	echo "<br>wtf, wo ist das errorhandling?!<br>";
	// errorhandling can be placed here
	exit;
}

class usermanagement extends basement{
##
# ToDo: Save userinformation in vars to save querys

	private $user = array(); // the hole userinfos from the database

	/**
	* loginUser
	*
	* Validates username, password and emailadress
	*
	* @param String $user	
	* @param String $email	
	* @param String $passwort	
	*
	* @return TRUE if login was successfull
	* @return FALSE if input is wrong
	*/
	public function loginUser($user, $email, $password){
		$db = parent::db_connect();
		$email_escaped = $db->real_escape_string($email);
		$sql = "SELECT
					`passwort_zusatz`,
					`passwort`
				FROM
					`login_profi`
				WHERE
					LOWER(`email`) = '".$email_escaped."' AND
					`aktiviert` = 1";
		$result = $db->query($sql);
		// IF the username was found, we test if email/password is correct
		if ($result->num_rows == 1){
			$datensatz = $result->fetch_array(MYSQLI_BOTH);
			$result->close();
			$user_escaped = $db->real_escape_string($user);
			$salt_add = $datensatz["passwort_zusatz"];
			$stored		= $datensatz["passwort"];
			// create password
			$string = hash_hmac("whirlpool", str_pad($password, strlen($password)*4, sha1($email_escaped), STR_PAD_BOTH), $salt_add, true );
			$stored_new = crypt($string, substr($stored, 0, 30));
			// create sql statement
			$sql = "SELECT
						`id`, `fehlversuche`
					FROM
						`login_profi`
					WHERE
						LOWER(`benutzername`) = '".$user_escaped."' AND
						LOWER(`email`) = '".$email_escaped."' AND
						`passwort` = '" .$stored_new."' AND
						`aktiviert` = 1";
			// execute statement
			$result = $db->query($sql);
			// IF one dataset was found, the username/password/emailadress is correct
			if ($result->num_rows == 1){
				// Reset internal counter for wrong logins
				$angriff = $result->fetch_array(MYSQLI_BOTH);
				$result->close();
				if ($angriff['fehlversuche'] != 0){
					$sql = "UPDATE
								`login_profi`
							SET
								`fehlversuche` = 0
							WHERE
								LOWER(`benutzername`) = '".$user_escaped."'
							LIMIT
								1";
					$db->query($sql);
				}
				// Everything is fine, return true
				return TRUE;
			}
			else{
				// password or emailadress was wrong, so we incremet the wrong results in the database
				$sql = "UPDATE
							`login_profi`
						SET
							`fehlversuche` = `fehlversuche` + 1
						WHERE
							LOWER(`benutzername`) = '".$user_escaped."'
						LIMIT
							1";
				$db->query($sql);
				// We look if the user failed to login at ten times ...
				$sql = "SELECT
							`fehlversuche`
						FROM
							`login_profi`
						WHERE
							LOWER(`benutzername`) = '".$user_escaped."'";
				$result = $db->query($sql);
				$anzahl = $result->fetch_array(MYSQLI_BOTH);
				$result->close();
				// ... and deactivate his account ...
				if ($anzahl['fehlversuche'] > 9){
					$sql = "UPDATE
								`login_profi`
							SET
								`fehlversuche` = 0,
								`aktiviert` = 0
							WHERE
								LOWER(`benutzername`) = '".$user_escaped."'
							LIMIT
								1";
					$db->query($sql);
				}
				return FALSE;
			}
		}
		$db->close();
	}

	/**
	* updateUser
	*
	* Updates the userinformation (IP, useragnet..) and set a cookie
	*
	* @param String $passwort	
	*
	* @return TRUE if infos where successfully saved and the cookie set
	*/
	function updateUser($user){
		$db = parent::db_connect();
		$sql = "UPDATE
					`login_profi`
				SET
					`ip` = '".$db->real_escape_string($_SERVER['REMOTE_ADDR'])."',
					`benutzerinfo` = '".$db->real_escape_string($_SERVER['HTTP_USER_AGENT'])."',
					`anmeldung` = '".$db->real_escape_string(md5($_SERVER['REQUEST_TIME']))."',
					`zuletzt_aktiv` = NOW()
				WHERE
					LOWER(`benutzername`) = '".$db->real_escape_string($user)."'
				LIMIT
					1";
		$db->query($sql);
		if ($db->affected_rows == 1){
			$_SESSION['angemeldet']   = true;
			$_SESSION['benutzername'] = $user;
			$_SESSION['anmeldung']    = md5( $_SERVER['REQUEST_TIME'] );
			return TRUE;
		}
		$db->close;
	}

	/**
	* checkUser
	*
	* We look if the visitor is a logged in user or a stranger
	*
	* @return TRUE if the visitor is a loged in user
	* @return FALSE if he is a stranger
	*/
	public function checkUser(){
		session_regenerate_id(true);
		if (isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] !== true){
			return FALSE;
		}
		elseif(!isset($_SESSION['angemeldet'])){
			return FALSE;
		}
		else{
			$db = parent::db_connect();
			$sql = "SELECT
						`ip`, `benutzerinfo`, `anmeldung`, UNIX_TIMESTAMP(`zuletzt_aktiv`) as zuletzt_aktiv
					FROM
						`login_profi`
					WHERE
						`benutzername` = '".$db->real_escape_string($_SESSION['benutzername'])."' AND
						`aktiviert` = 1";
			$result = $db->query($sql);
			if ($result->num_rows == 1){
				$userinfo = $result->fetch_array(MYSQLI_BOTH);
				$result->close();
				if ($userinfo['ip'] !== $_SERVER['REMOTE_ADDR']) return FALSE;
				if ($userinfo['benutzerinfo'] !== $_SERVER['HTTP_USER_AGENT']) return FALSE;
				if ($userinfo['anmeldung'] !== $_SESSION['anmeldung']) return FALSE;
				if (($userinfo['zuletzt_aktiv'] + 600) <= $_SERVER['REQUEST_TIME']) return FALSE;
			}
			else{
				return FALSE;
			}
			$sql = "UPDATE
						`login_profi`
					SET
						`zuletzt_aktiv` = NOW()
					WHERE
						LOWER(`benutzername`) = '".$db->real_escape_string($_SESSION['benutzername'])."'
					LIMIT
						1";
			$db->query($sql);
			$db->close();
			return TRUE;
		}
	}

	/**
	* resetUser
	*
	* We logout the user
	*/
	public function resetUser(){
		session_destroy();
		header("location: ?site=welcome&do=hello");
		exit;
	}

	/**
	* register
	*
	* User can signup
	*
	* @param string $user
	* @param string $email
	* @param string $password	
	* @param int		$rounds		how good should the password be saved?
	*
	* @return TRUE/FALSE if the user was successfully added to the db or not
	*/
	public function register($user, $email, $password, $rounds){
		global $error;
		$db = parent::db_connect();
		if($db !== FALSE){
			$salt_add = md5(microtime());
			if($rounds == ""){
				$rounds = "08"; 
			}
			$user_escaped = $db->real_escape_string($user);
			$email_escaped = $db->real_escape_string($email);
			$string = hash_hmac("whirlpool", str_pad($password, strlen($password)*4, sha1($email_escaped), STR_PAD_BOTH), $salt_add, true);
			$salt = substr(str_shuffle('./0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ),0,22);
			$pw_hash = crypt($string, '$2a$'.$rounds.'$'.$salt);
			$sql = "INSERT INTO
								`login_profi` (	`benutzername`, 
																`passwort`, 
																`passwort_zusatz`, 
																`email`,
																`ip`, 
																`benutzerinfo`, 
																`anmeldung`, 
																`zuletzt_aktiv`, 
																`fehlversuche`, 
																`salt_rounds`,
																`aktiviert`
															)
												VALUES(	'".$user_escaped."', 
																'".$pw_hash."',
																'".$salt_add."',
																'".$email_escaped."',
																'".$_SERVER['REMOTE_ADDR']."',
																'".$_SERVER['HTTP_USER_AGENT']."',
																'".$_SERVER['REQUEST_TIME']."',
																'0000-00-00 00:00:00',
																'0',
																'".$rounds."',
																'1')"; 													
			if($db->query($sql) === FALSE){
				$error = "something is wrong ".$db->error;
				return false;
			}
			if ($db->affected_rows == 1){
				$error = 'User was successfully added to the database.';
				return true;
			}
			else{
				$error =  'User wasn\'t successfully added to the database!';
				return false;
			}
		}
		else{
				$error =  'database failed because of: <br> <span class="hinweis">' .mysqli_connect_errno(). ' : ' .mysqli_connect_error(). '</span>';
				return false;
		}
		$db->close();
	}

	/**
	* checkPassword
	*
	* Checks the strength of the password
	*
	* @param string $password	
	*
	* @return TRUE/FALSE if the password is strength enough or not
	*/	
	public function checkPassword($password){
		global $FailMsg;
		$Strength = 0;
		if($password === ""){
			$FailMsg = "Password is empty";
			return FALSE;
		}
		else{
			// small latters?
			if(preg_match("/[a-z]+/", $password)){
					$Strength++;
			}
			// big latters?
			if(preg_match("/[A-Z]+/", $password)){
					$Strength++;
			}
			// numbers?
			if(preg_match("/\d+/", $password )){
					$Strength++;
			}
			// special chars?
			if(preg_match("/\W+/", $password )){
					$Strength++;
			}
			// length?
			if(strlen($password) >= 6 && strlen($password) <= 15){
				$Strength++;
			}
			// long length 
			elseif(strlen($password) > 15){
				$Strength = $Strength + 2;
			}
			// short length
			elseif(strlen($password) < 6){
					$Strength = 1;
			}
			// $Strength strong enough?
		}
		if($Strength < 4){
			$FailMsg = "Pasword didn't reach enaugh security rulz";
			return FALSE;
		}
		else{
			$FailMsg = "";
			return TRUE;
		}
	}
	
	/**
	* checkUsername
	*
	* Checks the strength of the password
	*
	* @param string		$username
	* @param boolean	$register	-	wtf is this? Remind: Comment code allways direct after writing...
	*
	* @return TRUE/FALSE if the username is free or not
	*/	
	public function checkUsername($username, $register){
		global $FailMsg;
		$FailMsg = "";
		if(empty($username)){
			$Msg = "Username is empty";
			return FALSE;
		}
		if(!is_string($username)){
			$Msg = "Username is in the wrong datatype, it is no string";
			return FALSE;
		}
		if($register){
			$db = parent::db_connect();
			$user_escaped = $db->real_escape_string($username);
			$sql = "SELECT
							`id`
						FROM
							`login_profi`
						WHERE
							LOWER(`benutzername`) = '".$user_escaped."'";
			$result = $db->query($sql);
			if(!$result->num_rows == 0){
				$Msg = "Username exists already";
				$result->close();
				$db->close();
				return FALSE;
			}
			$result->close();
			$db->close();
		}
		return TRUE;
	}

	private function fetchUserInfo(){
		if($this->checkUser()){
			// todo: fetch userinfo from db, put it into array $user and return
			$this->user["style"] = "generic";
			return TRUE;
		}
		else{
			return FALSE; 
		}
	}

	public function getUserInfo(){
		if($this->fetchUserInfo()){
			return $this->user;
		}
		else{
			return FALSE; 
		}
	}
}
?>