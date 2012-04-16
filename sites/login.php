<?php
if (!isset($_SESSION['server_SID'])){
	session_unset();
	$_SESSION = array();
	session_destroy();
	session_start();
	session_regenerate_id();
	$_SESSION['server_SID'] = true;
}
$_SESSION['angemeldet'] = false;
$isUser              		= false;
$update                 = false;

$Login = new usermanagement();
$Style = new templateEngine($Login);
$Style->setContent("title", "Test-Titel");
$Style->setTemplate("login.tpl"); 
$Style->setContent("username", "Benutzername");
$Style->setContent("password", "Passwort");
$Style->setContent("email", "eMailadresse");
	
if (isset($_POST['login'])){
	
	$FailMsg = "";
	$isUser = $Login->loginUser($_POST['username'], $_POST['email'], $_POST['password']);
	if ($isUser){
		$update = $Login->updateUser($_POST['username']);
		if ($update){
			$Style->setContent("login", "<a href='?site=welcome'>Login erfolgreich, hier gehts zur Startseite</a>");
			//echo"<a href='?site=welcome'>Login erfolgreich, hier gehts zur Startseite</a>";
			//header( 'location: ?site=welcome' );
			// fix this shit, damn redirects doesn't work
			exit;
		}
		else{
			$Style->setContent("login", "<h3>Bei der Anmeldung ist ein Problem aufgetreten!</h3>");
			//$Output = '<h3>Bei der Anmeldung ist ein Problem aufgetreten!</h3>';
		}
	}
	else{
		$Style->setContent("login", "<h3>Die Anmeldung war fehlerhaft!</h3>");
		//$Output = '<h3>Die Anmeldung war fehlerhaft!</h3>';
	}
}
else{
	$Style->setContent("login", " ");
}
$Style->parseTemplate();
$Output = $Style->getTemplate();
?>
