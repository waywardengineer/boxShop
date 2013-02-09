<?php
require_once('cal_parseEvents.php');
if (isset($_GET['categoryID']) and $_GET['categoryID'] != 'all'){
	$categoryID = validate($_GET['categoryID']);
}
else {
	$categoryID = false;
}
if (isset($_GET['locationID']) and $_GET['locationID'] != 'all'){
	$locationID = validate($_GET['locationID']);
}
else {
	$locationID = false;
}
$showedit = $user->isTrusted();

$month=isset($_GET['month'])?validate($_GET['month']):date('n',time());
$year=isset($_GET['year'])?validate($_GET['year']):date('Y',time());
$nextMonth=($month==12?1:$month+1);
$nextMonthsYear=($month==12?$year+1:$year);
$lastMonth=($month==1?12:$month-1);
$lastMonthsYear=($month==1?$year-1:$year);


$starttime=mktime(0,0,0,$month,0,$year);
$endtime=mktime(0,0,0,$nextMonth,0,$nextMonthsYear);
$sqlparams = " cal_events.starttime >= $starttime";
if ($categoryID){
	$sqlparams .= " AND cal_events.category = $categoryID";
}
if ($locationID){
	$sqlparams .= " AND cal_events.location = $locationID";
}
$events=new events($sqlparams);
$events->constrainToDate($starttime, $endtime);

if ((isset($events->events) or isset($_GET['month']) or isset($_GET['calview'])) and !isset($_GET['listview'])){
	echo makeCalendar();
}
else {
	echo makeList();
}
$categoryID = false;
$locationID = false;
function makeCalendar(){
	global $events, $nextMonth, $nextMonthsYear, $lastMonth, $lastMonthsYear, $month, $year, $categoryID, $locationID, $showedit;
	$calHTML=new eventTemplate('calendar');
	$startday=date("w",mktime(0,0,0,$month,1,$year));
	$dayloop=1;
	$calSizeParams = array('eventMaxHeight'=>60, 'dayHeight'=>78, 'dayWidth'=>67, 'dayHeadingHeight'=>25, 'hSpacing'=>3, 'vSpacing'=>6);
	$top=0;
	$left=0;
	$days=array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
	$output='';
	$cellwidth = ($calSizeParams['dayWidth'] + $calSizeParams['hSpacing']);	
		foreach($days as $day){

		$output .= $calHTML->calendarGetHTML('dayHeading', $day, 'style="left:' .  $left . 'px; top:0px; height:' . $calSizeParams['dayHeadingHeight'] . 'px; width:' . $cellwidth . 'px;"');
		$left += $cellwidth;
	}
	$top=$calSizeParams['dayHeadingHeight'];
	$monthName=date('M', mktime(0,0,0,$month,1,$year));
	while (checkdate($month,$dayloop,$year)) {
		if($dayloop==1) {$dayloop=1-$startday;}
		$output .= $calHTML->calendarGetHTML('weekDivider');
		$left = 0;
		for ($wd=0;$wd<7;$wd++) {
			$style='style="left:' .  $left . 'px; top:' . $top . 'px; height:' . $calSizeParams['dayHeight'] . 'px; width:' . $calSizeParams['dayWidth'] . 'px;"';
			if (!checkdate($month,$dayloop,$year)) {
				$output .= $calHTML->calendarGetHTML('notDay', '', $style);
			}
			else {
				$starttime=mktime(0,0,0,$month,$dayloop,$year);
				$endtime=$starttime+(24*3600);
				$events->resetConstraints();
				$events->constrainToDate($starttime, $endtime);
				if ($events->events) {
					$numevents = count($events->events);
					$eventHeight=intval($calSizeParams['eventMaxHeight']/$numevents - 0.4);
					$template=($numevents == 1)?'calendarEventSingle':'calendarEventMulti';
					$eventHTML=$events->do_display_events('g:i A', '<br />', $template, $showedit);
					$output2 = "<span class=\"calendarEventDayDayMouseover\">$monthName $dayloop, $year</span><span class=\"calendarEventDayDate\">$dayloop</span>" . str_replace('{height}', $eventHeight, $eventHTML);
					$eventchars=strlen($output2);
					$style.=" onMouseOver=\"expandCalEvent('$dayloop', $left, " . $cellwidth . ", $eventchars)\" onMouseOut=\"restoreCalEvent('$dayloop', $left, " . $calSizeParams['dayWidth'] . ',' . $calSizeParams['dayHeight'] .")\" id=\"$dayloop\"";					
					$output .= $calHTML->calendarGetHTML('eventDay', $output2, $style);
				}
				else {
					$output .= $calHTML->calendarGetHTML('Day', $dayloop, $style);
				}
			}
			$dayloop++;
			$left += $cellwidth;
		}
		$top += ($calSizeParams['dayHeight'] + $calSizeParams['vSpacing']);
	
	}
	$calHTML->set('calendar',$output);
	$output="jQuery('#calendardiv').load('cal_calendar.php?locationID=$locationID&categoryID=$categoryID&month=$lastMonth&year=$lastMonthsYear')";
	$calHTML->set('prevaction',$output);
	$output="jQuery('#calendardiv').load('cal_calendar.php?locationID=$locationID&categoryID=$categoryID&month=$nextMonth&year=$nextMonthsYear')";
	$calHTML->set('nextaction',$output);
	$output = $monthName . ', ' . $year;
	$calHTML->set('thismonth',$output);
	$calHTML->set('height',$top);
	$output = $cellwidth*7;
	$calHTML->set('width',$output);
	$output="jQuery('#calendardiv').load('cal_calendar.php?locationID=$locationID&categoryID=$categoryID&listview=1')";
	$calHTML->set('showlist', $output);
	if ($categoryID){
		$calHTML->set('category',$events->allcategories[$categoryID]);
	}
	else{
		$calHTML->set('category','All');
	}
	if ($locationID){
		$calHTML->set('location',$events->alllocations[$locationID]);
	}
	else{
		$calHTML->set('location','All');
	}
	return $calHTML->doOutput();
}
function makeList(){
	global $events, $nextMonth, $nextMonthsYear, $lastMonth, $lastMonthsYear, $month, $year, $categoryID, $locationID, $showedit;
	$events->resetConstraints();
	$events->constrainToDate(time());
	$listHTML = new eventTemplate('eventlist');
	$listHTML->set('eventlist', $events->do_display_events('M j, Y g:i A', '<br />', 'eventlistevent', $showedit));
	$output='';
	if ($categoryID){
		$listHTML->set('category',$events->allcategories[$categoryID]);
	}
	else{
		$listHTML->set('category','All');
	}
	if ($locationID){
		$listHTML->set('location',$events->alllocations[$locationID]);
	}
	else{
		$listHTML->set('location','All');
	}
	$output="jQuery('#calendardiv').load('cal_calendar.php?locationID=$locationID&categoryID=$categoryID&calview=1')";
	$listHTML->set('showlist', $output);
	return $listHTML->doOutput();
}
function validate($str){
	return stripslashes($str);
}
	

