<?php
$authkey='boxshop94124';
include("include/common.php");
if (!$user->isTrusted()){
	header("Location: index.php");
	die();
}
$html->addSubTemplate('templates/cnc.tpl');
include("include/alarm.php");
if($user->isTrusted()){
		
		
	$resultsArray = array(array("materialID", "thicknessID", "Material", "Thickness", "Amperage", "Voltage", "Feedrate", "Pierce Height", "Initial Cut Height", "Pierce Delay", "PSI", "Kerf", "Notes", "Done by", "Date"));
	$query="SELECT cnc_settings.ID AS ID, materialID, thicknessID, material, thickness, amps, volts, feedrate, pierceHeight, initCutHeight, pierceDelay, PSI, kerf, notes FROM cnc_settings JOIN cnc_materials ON cnc_materials.ID = materialID JOIN cnc_thicknesses ON cnc_thicknesses.ID = thicknessID";
	$result = $database->query($query);
	while ($row = @mysql_fetch_array($result, MYSQL_NUM)){
		$processedRow = $row;
		$settingId = array_shift($processedRow);
		$query = "SELECT DISTINCT username FROM users JOIN cnc_uses USING (UID) WHERE settingID = $settingId LIMIT 5";
		$result2 = $database->query($query);
		$txt='';
		while($row2 = @mysql_fetch_array($result2)){
			$txt .= (($txt == '')?'':', ') . $row2['username'];
		}
		$processedRow[] = $txt;
		$txt = '';
		$query = "SELECT timestamp FROM cnc_uses WHERE settingID = $settingId ORDER BY timestamp DESC LIMIT 1";
		$result2 = $database->query($query);
		if ($result2) {
			$row2 = @mysql_fetch_array($result2);
			$txt = date('M j Y', $row2['timestamp']);
		}

		$processedRow[] = $txt;
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
echo $html->doOutput();

