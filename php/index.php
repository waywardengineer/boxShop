<?php
$authkey='';
date_default_timezone_set('America/Los_Angeles');
include("include/user.php");
include("include/template.php");
include("include/alarm.php");
$showsections = array('page_index');
$html = new Template('templates/main.tpl');
$alarm = new Alarm();



if($user->logged_in){
	$welcome_msg = "Welcome, $user->username";
	$toplink['Logout'] = 'process.php';
}
else {
	$welcome_msg="Hello, guest";
	$html->set('form_login_user', $form->value("user"));
	$html->set('form_login_user_err', $form->error("user"));
	$html->set('form_login_pass', $form->value("pass"));
	$html->set('form_login_pass_err', $form->error("pass"));
	$showsections[]='login';
}

$alarmstatus=$alarm->getstatus();
if($user->isTrusted()){
	$showsections[] = 'page_index2';
	if ($_POST['actionType']){
		$actionType = (int) $_POST['actionType'];
		$expiration = time() + 5*60;
		$q = "UPDATE pendingactions SET state = 1, expiration = $expiration WHERE ID = $actionType";
		$database->query($q);
	}
	$guestcodes = new Guestcodes();
	$toplink['Temporary Doorcodes'] = 'codes.php';
	$toplink['My Account'] = 'edit.php';
	$showsections[]='trusted';
	if($user->isAdmin()){
		$showsections[]='admin';
		$toplink['Admin']= 'admin/admin.php';
	}
	$alarm->clearExpiredActions();

	if ($alarmstatus < 6){ //make buzz door button if communication
		$q="SELECT state FROM pendingactions WHERE ID = 1";
		$row=@mysql_fetch_array($database->query($q));	
		$html->makeButton($row['state'], 1);
	}
	if ($alarmstatus == 4){ // make stop alarm button if in alarm mode
		$q="SELECT state FROM pendingactions WHERE ID = 2";
		$row=@mysql_fetch_array($database->query($q));
		$html->makeButton($row['state'], 2);
	}
	
	
}






$html->makestatus($alarmstatus, $user->logged_in);
$html->makeLinkBars($toplink, 'toplinks');
$html->makeLinkBars($bottomlink, 'bottomlinks');
$html->set('welcome', $welcome_msg);
echo $html->doOutput($showsections);

