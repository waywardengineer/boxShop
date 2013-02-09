<?php
$auth='auth';
include("include/common.php");
$html = new Template('templates/main.tpl');
$html->createNav();

if(!$user->isTrusted()){
	die();
}
include("include/alarm.php");
$alarm = new Alarm();
$guestcodes = new Guestcodes();
$now=time();

if (isset($_GET['log'])){
	switch($_GET['log']){
		case 'event':
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
			$html->set('content', $html->makeEventLog($result, $user->isAdmin()));
			break;
		case 'year':
			$currentTimeInterval = mktime(0,0,0, date('m',$now),date('d',$now), date('Y',$now)) - 364*24*3600;
			$interval = 7*24*3600;
			$doorCountResults = makeDoorCountArray($currentTimeInterval, $interval);
			$html->set('content', $html->makeBarGraph($doorCountResults['doorCounts'], $doorCountResults['max'], 4, 'Yard door openings over the last year'));			
			break;
		case 'day':
			$currentTimeInterval = mktime(date('H',$now), 0,0, date('m',$now),date('d',$now), date('Y',$now)) - 24*3600;;
			$interval = 2*3600;
			$doorCountResults = makeDoorCountArray($currentTimeInterval, $interval);
			$max = ($doorCountResults['max'] < 10)?10:$doorCountResults['max'];
			$html->set('content', $html->makeBarGraph($doorCountResults['doorCounts'], $max, 1, 'Yard door openings over the last 24 hours'));			
			break;
		case 'week':
			$currentTimeInterval = mktime(0,0,0, date('m',$now),date('d',$now), date('Y',$now)) - 6*24*3600;
			$interval = 24*3600;
			$doorCountResults = makeDoorCountArray($currentTimeInterval, $interval);
			$html->set('content', $html->makeBarGraph($doorCountResults['doorCounts'], $doorCountResults['max'], 1, 'Yard door openings over the last week'));			
			break;


	}

}

echo $html->doOutput();

function makeDoorCountArray($currentTimeInterval, $step){
	global $now, $database;
	$doorCounts = array();
	$totals = array();
	while ($currentTimeInterval < $now){
		$timeIntervalEnd = $currentTimeInterval + $step;
		$q="SELECT COUNT(ID) AS total FROM alarmevents WHERE timestamp > $currentTimeInterval AND timestamp <= $timeIntervalEnd AND componentID = 'D' AND state = 1";
		$row=@mysql_fetch_array($database->query($q));
		$doorCounts[] = array(date('M j', $currentTimeInterval), $row['total']);
		$totals[] = $row['total'];
		$currentTimeInterval = $timeIntervalEnd;
	}
	return array('doorCounts'=>$doorCounts, 'max' => max($totals));
}

