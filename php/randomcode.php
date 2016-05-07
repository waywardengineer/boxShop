<?php

$authkey='boxshop94124';
include("include/database.php");
include("include/alarm.php");
$guestcodes = new Guestcodes();
$goodCode = false;
while (!$goodCode){
	$code = '';
	for ($i=0; $i<5; $i++){
		$code.= mt_rand(0, 9);
	}
	$result = $guestcodes->validateAndConvert($code, 0);
	if ($result['err'] == 0){
		$goodCode = true;
	}
}
echo json_encode(array('code'=>$code));
		
