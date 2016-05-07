<?php

$authkey='boxshop94124';
include("../include/database.php");
include("../include/alarm.php");

$guestcodes = new Guestcodes();
$result = array();
if (isset($_GET['token']) && $_GET['token'] == API_TOKEN){
	$q='SELECT data FROM settings WHERE setting="currentCodeHash"';
	$row=@mysql_fetch_array($database->query($q));		
	if (isset($_GET['hash']) && $_GET['hash'] != $row['data']){
		$result['codesJson'] = $guestcodes->compileCodeJson();
		$result['hash'] = $row['data'];
	}
}
echo json_encode($result);
