<?php 
$authkey='boxshop94124';
include("include/common.php");
$html->addSubTemplate('templates/calendar.tpl');
$pocessedArray = array();
if (isset($_REQUEST)){
	foreach ($_REQUEST as $k=>$v){
		$processedArray[mysql_real_escape_string($k)] =  mysql_real_escape_string($v);
	}
}
$month=isset($_GET['m'])?$processedArray['m']:date('n',time());
$year=isset($processedArray['y'])?$processedArray['y']:date('Y',time());
$nextMonth=($month==12?1:$month+1);
$nextMonthsYear=($month==12?$year+1:$year);
$lastMonth=($month==1?12:$month-1);
$lastMonthsYear=($month==1?$year-1:$year);
$saved = false;
$errors = false;
if (isset($_POST['formsubmitted']) && $user->istrusted()){
	$errors = processForm();
	if (!$errors){
		$saved=true;
	}
}
if (isset($_GET['action']) && !$saved && $user->isTrusted()  && $_POST['formsubmitted']!='delete'){
	switch ($_GET['action']){
		case 'addsingle':
			$subTemplate = new Template('templates/cal_editsingle.tpl');
			$subTemplate->setMulti(array('formsubmitted' => 'single', 'formaction' => 'addsingle'));
		break;
		case 'addmulti':
			$subTemplate = new Template('templates/cal_editmulti.tpl');
		break;
		case 'edit':
			$subTemplate = new Template('templates/cal_editsingle.tpl');
			$subTemplate->setMulti(array('formsubmitted' => 'edit', 'editid'=>intval($_REQUEST['id']), 'formaction' => 'edit'));
			fillform(validate($_GET['id']));
		break;
	}
	if (isset($_POST['formsubmitted'])){
		$formfields=array('title', 'day', 'start', 'repeatday', 'description', 'repeatfrequency', 'repeatcount', 'length');
		foreach ($formfields as $formfield){
			$str = 'form' . $formfield;
			$subTemplate->set($str, $processedArray[$formfield]);
		}
		$str=$_POST['ampm'] . 'selected';
		$subTemplate->set($str, 'selected="selected" ');
		foreach ($errors as $error){
			$value='<span style="color:#ff0000">*</span>';
			if ($error['field']){
				$id=$error['field'] . 'asterisk';
				$subTemplate->set($id, $value);
			}
		}
	}
	if (isset($subTemplate)){
		$html->set('calForms', $subTemplate->doOutput());
	}

			   
		
}
if ($saved){
	if ($_POST['formsubmitted']=='delete'){
		$html->set('message', 'Deletion Successful');
	}
	else {
		$html->set('message', 'Event Saved!');
	}
}
if ($errors){
	$output='';
	foreach ($errors as $error){
		$output .='<p><strong>' . $error['description'] . '</strong></p>';
	}
	$html->set('message', $output);
}
echo $html->doOutput();



function processForm(){
	global $menunames, $user, $month, $year;
	if ($_POST['formsubmitted']=='delete'){
		$errors[]=array('description'=>"Couldn't delete event");
		if ($user->userlevel==9){
			$querystring="DELETE FROM cal_events WHERE ID = " . intval($_POST['editid']);
			if (@mysql_query($querystring)){
				$errors=false;
			}
		}
		return $errors;
	}
	$formfields=array('title', 'day', 'start', 'length', 'location', 'category', 'repeatday', 'description', 'repeatfrequency', 'repeatcount', 'newlocation', 'newcategory', 'ampm');
	if ($_POST['formsubmitted']=='single' or $_POST['formsubmitted']=='edit'){
		$required=array('title', 'day', 'start', 'length', 'description');
	}
	else {
		$required=array('title', 'description', 'repeatcount', 'start', 'length');
	}
	$errors=array();
	$inputs = array();
	foreach($formfields as $formfield){
		if (isset($_POST[$formfield])) {
			$inputs[$formfield] =  htmlspecialchars($_POST[$formfield]); 
		}
		else if (in_array($formfield, $required)){
			$errors[]=array('description'=>'You forgot to fill out one of the needed fields on the form', 'field'=>$formfield);
		}
	}
	/*foreach($menunames as $item){
		$str='new' . $item;
		if ($inputs[$item]=='new'){
			if (!$inputs[$str]){
				$errors[]=array('description'=>'You forgot to fill out one of the needed fields on the form', 'field'=>$str);
			}
			else {
				$querystring='INSERT INTO cal_' . $item . " (description) VALUES ('" . $inputs[$str] . "');";
				@mysql_query($querystring);
				$querystring = 'SELECT ID FROM cal_' . $item . " WHERE description = '" . $inputs[$str] . "';"; 
				$result = @mysql_query($querystring);
				$row=@mysql_fetch_array($result, MYSQL_ASSOC);
				if ($row['ID']){
					$inputs[$item]=$row['ID'];
				}
				else {
					$errors[]=array('description'=>"There was an error saving the new $item", 'field'=>$str);
				}
			}
		}
		if ($inputs[$item]=='none'){
			$errors[]=array('description'=>"You forgot to specify a $item", 'field'=>$item);
		}
			
	}*/
	if ($_POST['formsubmitted']=='single' || $_POST['formsubmitted']=='edit'){
		$str = $inputs['day'] . ' ' . $inputs['start'] . $inputs['ampm'];
		$starttime=strtotime($str);
		if ($starttime){
			$endtime = $starttime + 3600 * $inputs['length'];
		}
		else {
			$errors[]=array('description'=>"Could not understand the date/time you entered", 'field'=>'day');
		}
	}
	if ($_POST['formsubmitted']=='multi'){
		$eventdates=array();
		$eventmonth=$month;
		$eventyear=$year;
		$timestr = $inputs['start'] . $inputs['ampm'];
		$failcount=0;
		if ($_POST['repeatfrequency']>0) {
			while (count($eventdates) <= $_POST['repeatcount'] && $failcount < 5) {
				$offset=$_POST['repeatday'] - date("w",mktime(0,0,0,$eventmonth,1,$eventyear))+1;
				if ($offset < 1) {$offset+=7;}
				$eventday = $offset+($_POST['repeatfrequency']-1)*7;
				$datestr= $eventmonth . '/' . $eventday . '/' . $eventyear . ' ' . $timestr;
				$eventdate = strtotime($datestr);
				if($eventdate >= time()){
					$eventdates[] = $eventdate;
					$failcount=0;
				}
				else {
					$failcount++;
				}
				if ($eventmonth==12) {
					$eventmonth=1;
					$eventyear++;
				}
				else {
					$eventmonth++;
				}
			}
		}
		elseif ($_POST['repeatfrequency']==0){
			$eventday=$_POST['repeatday'] - date("w",mktime(0,0,0,$month,1,$year))+1;
			if ($eventday < 1) {$eventday+=7;}
			while (count($eventdates) <= $_POST['repeatcount'] && $failcount < 5) {
				if (!checkdate($eventmonth,$eventday,$eventyear)) {						
					if ($eventmonth==12) {
						$eventmonth=1;
						$eventyear++;
					}
					else {
						$eventmonth++;
					}
					$eventday=$_POST['repeatday'] - date("w",mktime(0,0,0,$eventmonth,1,$eventyear))+1;
					if ($eventday < 1) {$eventday+=7;}
				}
				$datestr= $eventmonth . '/' . $eventday . '/' . $eventyear . ' ' . $timestr;
				$eventdate = strtotime($datestr);
				if($eventdate >= time()){
					$eventdates[] = $eventdate;
					$failcount=0;
				}
				else {
					$failcount++;
				}
				$eventday+=7;
			}
		}
		elseif ($_POST['repeatfrequency']==-1){
			while (count($eventdates) <= $_POST['repeatcount'] && $failcount < 5) {
				if ($eventmonth==12) {
					$eventmonth=1;
					$eventyear++;
				}
				else {
					$eventmonth++;
				}
				$offset=$_POST['repeatday'] - date("w",mktime(0,0,0,$eventmonth,1,$eventyear));
				if ($offset >= 0) {$offset-=7;}
				$datestr= $eventmonth . '/1/' . $eventyear . ' ' . $timestr;
				$eventdate=strtotime($datestr)+($offset)*24*3600;
				if($eventdate >= time()){
					$eventdates[] = $eventdate;
					$failcount=0;
				}
				else {
					$failcount++;
				}
			}
		}
		if (!$eventdates){
			$errors[]=array('description'=>"Could not create event dates", 'field'=>'');
		}
	}
	
	
	if (count($errors)==0){
		if ($_POST['formsubmitted']=='single'){
			$querystring = "INSERT INTO cal_events (starttime, endtime, category, location, title, description, addedby) VALUES (
				$starttime, $endtime, 0, 0, '" . $inputs['title'] . "', '" . $inputs['description'] . "', '" . $user->username . "');";
			if (@mysql_query($querystring)){
				$errors = false;
			}
			else {
				$errors[]=array('description'=>"There was an unidentified error saving your event", 'field'=>'');
			}
		}
		if ($_POST['formsubmitted']=='edit'){
			$querystring = "UPDATE cal_events SET starttime=$starttime, endtime=$endtime, category=0, 
			location=0, title='" . $inputs['title'] . "', description='" . $inputs['description'] . "', addedby = '" . $user->username ."'
			WHERE ID = " . intval($_POST['editid']);

			if (@mysql_query($querystring)){
				$errors = false;
			}
			else {
				$errors[]=array('description'=>"There was an unidentified error saving your event", 'field'=>'');
			}
		}
		if ($_POST['formsubmitted']=='multi'){
			foreach($eventdates as $starttime){
				$endtime = $starttime + 3600 * $inputs['length'];
				$querystring = "INSERT INTO cal_events (starttime, endtime, category, location, title, description, addedby) VALUES (
				$starttime, $endtime, 0, 0, '" . $inputs['title'] . "', '" . $inputs['description'] . "', '" . $user->username . "');";
				if (@mysql_query($querystring)){
					$errors = false;
				}
				else {
					$errors[]=array('description'=>"There was an unidentified error saving your event", 'field'=>'');
				}
			}
		}
		
		
	}
	return $errors;
}

function fillform($id){
	global $subTemplate, $user;
	$querystring="SELECT * FROM cal_events WHERE ID = $id";
	$result=@mysql_query($querystring);
	$row=@mysql_fetch_array($result, MYSQL_ASSOC);
	if ($row){
		$duration = ($row['endtime']-$row['starttime'])/3600;
		$day = date('n/j/y', $row['starttime']);
		$start = strftime('%I:%M', $row['starttime']);
		$ampm = strftime('%P', $row['starttime']) . 'selected';
		$subTemplate->setMulti(array('formtitle'=>$row['title'], 'formday'=>$day, 'formstart'=>$start, $ampm=>'selected="selected" ', 'formlength'=>$duration, 'formdescription'=>$row['description'], 'editid'=>$row['ID']));
		$currentcats=array('location'=>$row['location'], 'category'=>$row['category']);
		if($user->userlevel==9){
			$subTemplate->set('delete', '<input type="button" value="delete event" onclick="deleteevent()" />');
		}
		return $currentcats;
	}
	
}
function validate($str){
	return stripslashes($str);
}
?>
