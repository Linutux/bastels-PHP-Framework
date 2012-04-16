<?php
if(!isset($_GET["do"])){
	/*$Output = "Hier kannst du deinen eigenen personalisierten RSS Reader einrichten<br>\n";
	$Output .= "<a href='http://de.wikipedia.org/wiki/RSS'>RSS - Was ist das?</a><br>\n";
	$Output .= "<a href='?site=rss&amp;do=show_all'>Chronologische Anordnung aller eingerichteten Feeds</a><br>\n";
	$Output .= "<a href='?site=rss&amp;=configure'>Eigene Feeds einstellen</a><br>\n";*/
	$template_fr = new smarty_fr();
	$template_fr->assignVar("name", "Admin");
	$template_fr->display("rss.tpl");
	
}
elseif($_GET["do"] == "configure"){
	if(isset($_POST["feedUrl"])){
		if(!is_string($_POST["feedUrl"])){
			$Output = "Die Eingabe ist keine Url :(";
		}
		else{
			$RSS = new rss();
			$test = $RSS->rssUrl2Db($_POST["feedUrl"]);
			echo "<pre>".print_r($test, true)."</pre>";
		}
		echo "<pre>".print_r($_POST, true)."</pre>";	
	}
	$Output = "<p>Folgende Feeds sind schon abonierbar:<br></p>\n";
	// list of all feeds that are inside db(web_title)
	
	/*$Output = "Fehlt dein Lieblingsfeed in dieser Liste? Dann f&uuml;ge ihn selbst hinzu (einer pro Zeile):";
	$Output .= "<form id='loginform' method='post'>\n";
	$Output .= "	<label for='username'>Benutzername: </label><input type='text' name='username' id='username' value='' /><br />\n";
	$Output .= "	<label for='password'>Passwort: </label><input type='password' name='password' id='password' value='' /><br />\n";
	$Output .= "	<label for='email'>eMailadresse: </label><input type='email' name='email' id='email' value='' /><br />\n";
	$Output .= "	<input type='submit' name='login' id='login' value='Anmelden' />\n";
	$Output .= "</form>\n";*/
	$template = new template_new();
	$template->setFormInput("URL zum Feed", "url", "feedUrl", "http://");
	$template->setFormInput("Eintragen", "submit", "login", "");
	$template->setFormInput("Zur&uuml;cksetzen", "reset", "reset", "");
	$template->setHeadline("Fehlt dein Lieblingsfeed in dieser Liste? Dann f&uuml;ge ihn selbst hinzu (einer pro Zeile):");
	$template->formCreate();
	$Output .= $template->getOutputHtml();
	//echo "<pre>".print_r($template->formInput, true)."</pre>";
	//$template->debugFormInput();
}

















?>