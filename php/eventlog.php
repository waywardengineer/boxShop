<?php
$authkey='';
include("include/user.php");
include("include/template.php");
include("include/alarm.php");
$html = new Template('templates/eventlog.tpl');
$alarm = new Alarm();
$guestcodes = new Guestcodes();
if(!$user->isTrusted()){
	die();
}
$q="SELECT alarmevents.ID, alarmevents.componentID, alarmevents.state AS state, alarmevents.timestamp AS timestamp, 
	statusdescriptions.type AS type, statusdescriptions.component AS component, statusdescriptions.description AS description FROM alarmevents 
	INNER JOIN statusdescriptions ON alarmevents.componentID = statusdescriptions.componentID AND alarmevents.state 
	= statusdescriptions.state ORDER BY alarmevents.timestamp DESC LIMIT 500";
$result=$database->query($q);
$html->makeEventLog($result);
echo $html->doOutput();

