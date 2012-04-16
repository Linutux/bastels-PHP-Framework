<?php
$Login = new usermanagement();
if(isset($_GET["do"])){
	if($_GET["do"] == "logout"){
  resetUser();
	}
	elseif($_GET["do"] == "hello"){
		
		$Style = new templateEngine($Login);
		//$Style->setTemplate("templates/generic/head.tpl"); 
		$Style->setTemplate("welcome.tpl"); 
		$Style->setContent("title", "Test-Titel");
		$Style->addBlock("NEWS");
		$Style->setBlockTemplate("NEWS", "post.tpl");
		$Style->setBlockContent("NEWS", "postTitle", "Herrzlich Willkommen auf meiner Website");
		$Style->setBlockContent("NEWS", "postContent", "Es freut mich das Sie den Weg zu meinem neuen Framework gefunden haben<br>\n 
																										Dieser Content wurde automatisch erzeugt\n");
		
		if ($Login->checkUser()){
			$Style->setBlockTemplate("NEWS", "post.tpl");
			$Style->setBlockContent("NEWS", "postTitle", "Du bist gerade eingeloggt, gz...<br>\n");
			$Style->setBlockContent("NEWS", "postContent", "<a href='?site=welcome&do=logout'>ausloggn</a><br>\n
																											<a href='?site=rss'>RSS Features</a><br>\n");
		}
		else{
			$Style->setBlockTemplate("NEWS", "post.tpl");
			$Style->setBlockContent("NEWS", "postTitle", "Titel Nr.2");
			$Style->setBlockContent("NEWS", "postContent", "<a href='?site=login'>einloggn</a><br>\n
																											<a href='?site=register'>registrieren</a><br>\n");
		}
		$Style->addBlock2Content("NEWS");
		$Style->parseTemplate();
		$testvar = new mysql();
		$Output = $Style->getTemplate();	
	}
	elseif(empty($_GET["do"])){
		header("location: ?site=welcome&do=hello");
	}
}
else{
	header("location: ?site=welcome&do=hello");
}


 ?>
