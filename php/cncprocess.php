<?php
$authkey='boxshop94124';

include ('include/user.php');
if (!$user->isTrusted()){die();}
if (isset($_POST)){
	//print_r($_POST);
	$id = false;
	$processedArray = array();
	foreach ($_POST as $k=>$v){
		$processedArray[mysql_real_escape_string($k)] =  $v?mysql_real_escape_string($v):'0';
	}
	$notes = array_pop($processedArray);
	$notes = $notes == '0'?'':$notes;
	$result = getIDOfRecord();
	if ($result['id']){
		$id = $result['id'];
		if ($result['notesChanged']){
			$query = 'UPDATE cnc_settings SET notes = "' . mysql_real_escape_string($_POST['notes']) . '" WHERE ID = ' . $id . ' LIMIT 1';
			//echo $query;
			$database->query($query);
		}
	}
	else {
		$keys = implode(', ', array_keys($processedArray));
		$values = implode(', ', $processedArray);
		$query = "INSERT INTO cnc_settings($keys, notes) VALUES ($values, '$notes')";
		//echo $query;
		$database->query($query);
		$result = getIDOfRecord();
		//echo $result;
		$id = $result['id'];
	}
	if ($id){
		$query = "INSERT INTO cnc_uses(settingID, UID, timestamp) VALUES ($id, {$user->uid}, " . time() . ')';
		//echo $query;

		$database->query($query);
	}
	header("Location: cnc.php");
			 
	
}
function getIDOfRecord(){
	global $database, $processedArray, $notes;
	$query = 'SELECT ID, notes FROM cnc_settings WHERE ';
	$first = true;
	$notesChanged = false;
	foreach($processedArray as $k=>$v){
		$query .= $first?'':' AND ';
		$query .= $k . '=' . $v;
		$first = false;
	}
	$result = $database->query($query);
	$row = @mysql_fetch_array($result);
	if ($row){
		$id = $row['ID'];
		if ($row['notes'] != $notes){
			$notesChanged = true;
		}
	}
	else {
		$id = false;
	}
	return array('id'=>$id, 'notesChanged'=>$notesChanged);
}
		