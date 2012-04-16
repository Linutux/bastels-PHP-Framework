<?php
class rss extends basement{

	public function rssUrl2Db($rssUrl){
		$db=parent::db_connect();
		$sql = "SELECT `link` FROM 'feeds'";
		$result = $db->query($sql);
		//$insert=$db->prepare($query);
		$datensatz = $result->fetch_array(MYSQLI_BOTH);
		$result->close();
		return $datensatz;
	}



















}
?>