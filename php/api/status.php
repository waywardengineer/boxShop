<?php

$authkey='boxshop94124';
include("../include/database.php");
include("../include/alarm.php");
$alarm = new Alarm();
$guestcodes = new Guestcodes();

$alarm->doStatusLog();
$alarm->clearExpiredActions();

$result=$database->query("SELECT ID FROM pendingactions WHERE state > 0");
if (mysql_num_rows($result)){
	$actionTxt = array(1 => '.D1', 2=> '.M3');
	while ($row = mysql_fetch_array($result)){
		$q = 'UPDATE pendingactions SET state = 0 WHERE ID = ' . $row['ID'];
		$database->query($q);
	}
}
		
		
