<?php

$authkey='';
date_default_timezone_set('America/Los_Angeles');

include("include/user.php");
include("include/template.php");
include("include/alarm.php");
if (!$user->isTrusted()){
	header("Location: index.php");
	die();
}
$html = new Template('templates/main.tpl');
$alarm = new Alarm();
$guestCodes=new Guestcodes();
$welcome_msg = "Welcome, $user->username";
$showsections = array('page_codes', 'calscripts', 'page_admin2');
$q="SELECT codes.ID, codes.UID, codes.startDate, codes.notes, codes.code, users.username FROM codes INNER JOIN users ON codes.UID = users.UID WHERE codes.UID = " .  $user->uid . " AND codes.endDate >= " . $guestCodes->startOfToday() . " ORDER BY codes.startDate ASC;";
$result=$database->query($q);
if(mysql_num_rows($result)){
	$html->makeGuestCodesList($result);
}
$toplink['Home'] = 'index.php';
if($user->isAdmin()){
	$toplink['Admin']= 'admin/admin.php';
}
$html->set('codeCode', $form->value("codeCode"));
$html->set('codeCode_err', $form->error("codeCode_err"));
$html->set('codeDate', $form->value("codeDate"));
$html->set('codeDate_err', $form->error("codeDate_err"));
$html->set('codeNotes', $form->value("codeNotes"));
$toplink['Logout'] = 'process.php';
$html->makeLinkBars($toplink, 'toplinks');
$html->set('welcome', $welcome_msg);
echo $html->doOutput($showsections);