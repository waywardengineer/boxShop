<?php
$authkey='boxshop94124';
include("include/common.php");

if(!$user->isTrusted()){
	header("Location: index.php");
	die();
}
$html->addSubTemplate('templates/logs.tpl');
include("include/alarm.php");
$alarm = new Alarm();
$guestcodes = new Guestcodes();
$now=time();
$heading = '';
$content = '';
if (isset($_GET['log'])){
	switch($_GET['log']){
		case 'event':
				$q="SELECT component, description, alarmevents.timestamp as time, 
					 username FROM (alarmevents 
					JOIN statusdescriptions USING(componentID, state))
					JOIN users USING (UID)
					ORDER BY time DESC LIMIT 1000";
			$result=$database->query($q);
			$outPutArray = array(array('Component', 'Description', 'Time', 'User'));
			while ($row = @mysql_fetch_array($result, MYSQL_NUM)){
				$row[2] = date('M j g:ia', $row[2]);
				$outPutArray[] = $row;
			}
			
			$content = $html->makeTable($outPutArray, false, true, false, 'eventLog');
			$heading = 'Last 1000 access system events';
			break;
		case 'year':
			$currentTimeInterval = mktime(0,0,0, date('m',$now),date('d',$now), date('Y',$now)) - 364*24*3600;
			$interval = 7*24*3600;
			$doorCountResults = makeDoorCountArray($currentTimeInterval, $interval, 'M J');
			$content = $html->makeBarGraph($doorCountResults['doorCounts'], $doorCountResults['max'], 4);
			$heading = 'Yard door openings over the last year';
			break;
		case 'day':
			$currentTimeInterval = mktime(date('H',$now), 0,0, date('m',$now),date('d',$now), date('Y',$now)) - 24*3600;;
			$interval = 2*3600;
			$doorCountResults = makeDoorCountArray($currentTimeInterval, $interval, 'g:ia');
			$max = ($doorCountResults['max'] < 10)?10:$doorCountResults['max'];
			$content = $html->makeBarGraph($doorCountResults['doorCounts'], $max, 1);			
			$heading = 'Yard door openings over the last 24 hours';
			break;
		case 'week':
			$currentTimeInterval = mktime(0,0,0, date('m',$now),date('d',$now), date('Y',$now)) - 6*24*3600;
			$interval = 24*3600;
			$doorCountResults = makeDoorCountArray($currentTimeInterval, $interval, 'D');
			$content =  $html->makeBarGraph($doorCountResults['doorCounts'], $doorCountResults['max'], 1);
			$heading = 'Yard door openings over the last week';
						
			break;


	}

}
$html->setMulti(array('title'=>$heading, 'logs'=>$content));				

echo $html->doOutput();

function makeDoorCountArray($currentTimeInterval, $step, $dateFormat){
	global $now, $database;
	$doorCounts = array();
	$totals = array();
	while ($currentTimeInterval < $now){
		$timeIntervalEnd = $currentTimeInterval + $step;
		$q="SELECT COUNT(ID) AS total FROM alarmevents WHERE timestamp > $currentTimeInterval AND timestamp <= $timeIntervalEnd AND componentID = 'D' AND state = 1";
		$row=@mysql_fetch_array($database->query($q));
		$doorCounts[] = array(date($dateFormat, $currentTimeInterval), $row['total']);
		$totals[] = $row['total'];
		$currentTimeInterval = $timeIntervalEnd;
	}
	return array('doorCounts'=>$doorCounts, 'max' => max($totals));
}

