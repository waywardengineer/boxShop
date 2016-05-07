<?php
$authkey='boxshop94124';
include("include/common.php");
include("include/alarm.php");
$alarm = new Alarm();
if($user->logged_in){
	$html->addSubTemplate('templates/front.tpl');
	$welcome_msg = "Welcome, $user->username";
}
else {
	$html->set('formLoginUser', $form->value("user"));
	$html->set('formLoginUserError', $form->error("user"));
	$html->set('formLoginPass', $form->value("pass"));
	$html->set('formLoginPassError', $form->error("pass"));
	$showsections[]='login';
}
$alarmStatus=$alarm->getstatus();
$alarmStatusDescriptions = array(1 => 'Not Armed', 2 => 'Armed', 3 => 'Waiting to Arm', 4 => 'Alarm',  5 => 'Resetting from alarm', 6 => 'Unable to connect to alarm');

if($user->isTrusted()){
	if (isset($_POST['actionType'])){
		$actionType = (int) $_POST['actionType'];
		$expiration = time() + 5*60;
		$q = "UPDATE pendingactions SET state = 1, expiration = $expiration WHERE ID = $actionType";
		$database->query($q);
	}
	$guestcodes = new Guestcodes();
	$showsections[]='trusted';
	$alarm->clearExpiredActions();

	if ($alarmStatus < 6){ //make buzz door button if communication
		$q="SELECT state FROM pendingactions WHERE ID = 1";
		$row=@mysql_fetch_array($database->query($q));	
		$html->makeButton($row['state'], 1);
	}
	if ($alarmStatus == 4){ // make stop alarm button if in alarm mode
		$q="SELECT state FROM pendingactions WHERE ID = 2";
		$row=@mysql_fetch_array($database->query($q));
		$html->makeButton($row['state'], 2);
	}
	$html->set('alarmstatus', $alarmStatusDescriptions[$alarmStatus]);
	$result = $database->query("SELECT item FROM supplies_items WHERE needednow = 1");
	$arr = array();
	while ($row = @mysql_fetch_array($result)){
		$arr[] = '-' .$row['item'];
		
	}
	$html->set('itemsneeded', implode('<br> ', $arr));
		
		
	
}
echo $html->doOutput($showsections);

