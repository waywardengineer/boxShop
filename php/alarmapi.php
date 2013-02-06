<?php

$authkey='';
include("include/database.php");
include("include/alarm.php");
$alarm = new Alarm();
$guestcodes = new Guestcodes();
$alarm->doStatusLog();
if ($_GET['C'] != '0'){
	$result=$guestcodes->validateAndConvert($_GET['C']);
	if ($result['err'] != 2){//2 means non-alphanumeric chars in code, shouldnt happen unless somebodys messing with us
		if($guestcodes->doCodeCheckAndLog($result['code']) == 1){
			echo '.D1'; //open door
		}
		else {
			echo '.D2'; //signal bad code
		}
	}
}
$alarm->clearExpiredActions();
$result=$database->query("SELECT ID FROM pendingactions WHERE state > 0");
if (mysql_num_rows($result)){
	$actionTxt = array(1 => '.D1', 2=> '.M3');
	while ($row = mysql_fetch_array($result)){
		echo $actionTxt[$row['ID']];
		$q = 'UPDATE pendingactions SET state = 0 WHERE ID = ' . $row['ID'];
		$database->query($q);
	}
}
		
		
