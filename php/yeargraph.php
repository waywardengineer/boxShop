<?php
$authkey='';
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
$now=time();
$currentTimeInterval = mktime(0,0,0, date(m,$now),date(d,$now), date(Y,$now)) - 364*24*3600;
$doorCounts = array();
$totals = array();
while ($currentTimeInterval < $now){
	$timeIntervalEnd = $currentTimeInterval + 7*24*3600;
	$q="SELECT COUNT(ID) AS total FROM alarmevents WHERE timestamp > $currentTimeInterval AND timestamp <= $timeIntervalEnd AND componentID = 'D' AND state = 1";
	$row=@mysql_fetch_array($database->query($q));
	
	$doorCounts[] = array(date('M j', $currentTimeInterval), $row['total']);
	$totals[] = $row['total'];
	$currentTimeInterval = $timeIntervalEnd;
}
$html->set('eventlog', $html->makeBarGraph($doorCounts, max($totals), 4, 'Yard door openings over the last year'));		

echo $html->doOutput();

