<?php
$authkey='boxshop94124';

include ('include/user.php');
if (!$user->isTrusted()){die();}
if (isset($_POST)){
	$id = false;
	$processedArray = array();
	$error = false;
	$newRecord = false;
	foreach ($_POST as $k=>$v){
		$processedArray[mysql_real_escape_string($k)] =  mysql_real_escape_string($v);
	}
	if (array_key_exists('itemIDneeded', $processedArray)){
		$query = 'UPDATE supplies_items SET needednow = 1 WHERE itemID = ' . $processedArray['itemIDneeded'];
		$database->query($query);
	}
	else if (array_key_exists('itemID', $processedArray)){
		if ($processedArray['categoryID'] == '0'){
			$query = "INSERT INTO supplies_categories (category) VALUES ('{$processedArray['newcategory']}')";
			if ($database->query($query)){
				$newRecord = true;
			}
			else {
				$error = true;
			}
		}
		if (!$error){
			if ($processedArray['itemID'] == '0'){
				$query = 'INSERT INTO supplies_items (categoryID, item) VALUES (';
				$query .= $newRecord?'LAST_INSERT_ID()':$processedArray['categoryID'];
				$query .= ", '{$processedArray['newitem']}')";
				$newRecord = true;
			}
			else {
				$query = 'UPDATE supplies_items SET needednow = 0 WHERE itemID = ' . $processedArray['itemID'];
				$newRecord = false;
			}
			if (!$database->query($query)){
				$error = true;
			}


		}
		if (!$error){
			$keys = array('UID', 'qty',  'notes');
			$keysStr = 'itemID';
			$values = $newRecord?'LAST_INSERT_ID()':$processedArray['itemID'];
			if ($processedArray['cost'] > 0) {
				$keys[] = 'cost';
			}
			foreach($keys as $v){
				$keysStr .= ", $v";
				$values .= ", '$processedArray[$v]'";
			}
			$keysStr .= ", timestamp";
			$values .=', ' . time();
			$query = "INSERT INTO supplies_purchases ($keysStr) VALUES ($values)";
			$database->query($query);

		}
	}
}
header("Location: supplies.php");
?>		