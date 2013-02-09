<?php

$auth='auth';
include("../include/database.php");
include("../include/alarm.php");
$guestcodes = new Guestcodes();
if (isset($_GET['recieved'])){
	$q='SELECT ID FROM codesyncsql WHERE done = 0 ORDER BY ID ASC LIMIT ' . $_GET['recieved'];
	$result=$database->query($q);
	while ($row = mysql_fetch_array($result)){
		$q="UPDATE codesyncsql SET done=1 WHERE ID = " . $row['ID'];
		$database->query($q);
		echo $q;
	}
	

}
else {
	$result=$database->query("SELECT query FROM codesyncsql WHERE done = 0");
	if (mysql_num_rows($result)){
		$output = '';
		while ($row = mysql_fetch_array($result)){
			$output .= html_entity_decode($row['query']) . '|';
		}
	}
	echo $output;
}
		
		
