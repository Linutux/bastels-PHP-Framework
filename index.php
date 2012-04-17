<?php
// something by bastelfreak, who knows...

// Erzwingen das Session-Cookies benutzt werden und die SID nicht per URL transportiert wird
ini_set( 'session.use_only_cookies', '1' );
ini_set( 'session.use_trans_sid', '0' );

define('IN_FRAMEWORK', true);

// Session starten
session_start();

$debug = true;

/* Start - Errorhandling */
  if($debug === true){
    ini_set("display_errors", "on");
  }
  else{
    ini_set("display_errors", "off");
  }
  error_reporting(E_ALL);
  ini_set('error_reporting', E_ALL);
/* End - Errorhandling */

require(dirname(__FILE__)."/int_res/bootstrap.php");
require(rootPathFr.usermanagement);
require(rootPathFr.templateEngine);
$Configuration = new Config;
$Praefix = $Configuration->Site_Praefix;
$Suffix = $Configuration->Site_Suffix;
$Basement = new basement;

if(!isset($_GET["site"]) && !isset($_GET["modul"])){	
	Header("Location: ?site=welcome");
	die();
}
elseif(isset($_GET["site"]) && file_exists($Praefix.$_GET["site"].$Suffix)){
  require($Praefix.$_GET['site'].$Suffix);
	if(isset($Output) && !empty($Output)){
		echo $Output;
	}
}
elseif(isset($_GET["modul"])){
	// alle variablen noch absichern
	if(isset($_GET["page"])){
		require("modules/".$_GET['modul']."/php/".$_GET['page'].".php");
	}
}	
else{
	echo "Something is wrong, please contact the admin at webmaster@framework.bastelfreak.de";
}
?>
