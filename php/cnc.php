<?php
$auth='auth';
include("include/common.php");
$html = new Template('templates/main.tpl', 'templates/cnc.tpl');
$html->createNav();
include("include/alarm.php");
if($user->logged_in){
	$welcome_msg = "Welcome, $user->username";
}
else {
	$welcome_msg="Hello, guest";
	$html->set('formLoginUser', $form->value("user"));
	$html->set('formLoginUserError', $form->error("user"));
	$html->set('formLoginPass', $form->value("pass"));
	$html->set('formLoginPassError', $form->error("pass"));
	$showsections[]='login';
}
if($user->isTrusted()){
		
		
	$resultsArray = array(array("materialID", "thicknessID", "Material", "Thickness", "Amperage", "Voltage", "Feedrate", "Pierce Height", "Initial Cut Height", "Pierce Delay", "PSI", "Kerf", "Notes", "Done by"));
	$query="SELECT cnc_settings.ID AS ID, materialID, thicknessID, material, thickness, amps, volts, feedrate, pierceHeight, initCutHeight, pierceDelay, PSI, kerf, notes FROM cnc_settings JOIN cnc_materials ON cnc_materials.ID = materialID JOIN cnc_thicknesses ON cnc_thicknesses.ID = thicknessID";
	$result = $database->query($query);
	while ($row = @mysql_fetch_array($result, MYSQL_NUM)){
		$processedRow = $row;
		
		$query = "SELECT DISTINCT username FROM users JOIN cnc_uses USING (UID) WHERE settingID = " . array_shift($processedRow) . " LIMIT 5";
		$result2 = $database->query($query);
		$userNames='';
		while($row2 = @mysql_fetch_array($result2)){
			$userNames .= (($userNames == '')?'':', ') . $row2['username'];
		}
		$processedRow[] = $userNames;
		$resultsArray[] = $processedRow;
	}
	$html->set('cncLog', $html->makeTable($resultsArray, 'CNC Logs', true, false, 'cncLogs'));
	$query = "SELECT ID, material FROM cnc_materials";
	$result = $database->query($query);
	$arr=array(0 => '');
	while ($row = @mysql_fetch_array($result)){
		$arr[$row['ID']] = $row['material'];
	}
	$html->set('materialOptions', $html->makeFormOptions($arr));
	$query = "SELECT ID, thickness FROM cnc_thicknesses";
	$result = $database->query($query);
	$arr=array(0 => '');
	while ($row = @mysql_fetch_array($result)){
		$arr[$row['ID']] = $row['thickness'];
	}
	$html->set('thicknessOptions', $html->makeFormOptions($arr));
}
$html->set('msg', $welcome_msg);
echo $html->doOutput(array('cncscripts'));

