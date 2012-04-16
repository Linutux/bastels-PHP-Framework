<?php
interface database{
	public function connect2Database();
	public function doQuery($query);
}

?>