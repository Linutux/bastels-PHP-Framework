<?php
$Login = new usermanagement;
$Style = new templateEngine($Login);

if(!empty($_POST)){
	
	//$eingabe = CleanInput();
	//if(!$Login->checkPassword($_POST["password"])){
	//	$Output = $FailMsg;
	//	exit;
	//}
	if(!$Login->checkUsername($_POST["username"], TRUE)){
		$Output = $FailMsg;
		exit;
	}
	require $rootPath."/ext_res/email_verify/email_verify_source.php";
	if(!validateEmail($_POST["email"])){
		$Output = "Your eMailadress is invalid";
		exit;
	}
	if($Login->register($_POST['username'], $_POST['email'], $_POST['password'], "08")){
		$Output = "Die Registrierung war erfolgreich<br>\n";
		$Output .= "<a href='?site=welcome&do=hello'>Hier geht es zur Startseite</a>\n";
	}
	else{
		echo $error;
	}
}
elseif(empty($_POST)){
	$Style->setTemplate("register_new.tpl");
	$Style->setContent("title", "Test-Titel");
	$Style->setContent("username", "Benutzername");
	$Style->setContent("password", "Passwort");
	$Style->setContent("email", "eMailadresse");
	$Style->parseTemplate();
	$Output = $Style->getTemplate();
}
?>

    
    
		
    
