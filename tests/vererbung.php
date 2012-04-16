<?php
ini_set("display_errors", "on");
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
class a {
	public $a = "aaa";
	protected $b = "bbb";
	private $c = "ccc";
}

class xyz extends a{
	public $tst = parent::a;

}


$new = new xyz():
echo $new->tst;




?>
<html>
	<head>
		<title>test</title>
	</head>

</html>