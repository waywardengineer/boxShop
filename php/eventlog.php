<?php
$auth='';
date_default_timezone_set('America/Los_Angeles');

include("include/user.php");
include("include/template.php");
include("include/alarm.php");
$html = new Template('templates/eventlog.tpl');
$alarm = new Alarm();
$guestcodes = new Guestcodes();
if(!$user->isTrusted()){
	die();
}
if ($user->isAdmin()){
	
	$q="SELECT alarmevents.ID, alarmevents.componentID, alarmevents.state AS state, alarmevents.timestamp, 
		statusdescriptions.type, statusdescriptions.component, statusdescriptions.description, users.username FROM alarmevents 
		INNER JOIN statusdescriptions ON alarmevents.componentID = statusdescriptions.componentID AND alarmevents.state 
		= statusdescriptions.state 
		INNER JOIN users ON users.UID = alarmevents.UID
		ORDER BY alarmevents.timestamp DESC LIMIT 500";
}
else {
	$q="SELECT alarmevents.ID, alarmevents.componentID, alarmevents.state AS state, alarmevents.timestamp, 
		statusdescriptions.type, statusdescriptions.component, statusdescriptions.description FROM alarmevents 
		INNER JOIN statusdescriptions ON alarmevents.componentID = statusdescriptions.componentID AND alarmevents.state 
		= statusdescriptions.state 
		ORDER BY alarmevents.timestamp DESC LIMIT 500";
}
	
$result=$database->query($q);
$html->makeEventLog($result, $user->isAdmin());
echo $html->doOutput();

