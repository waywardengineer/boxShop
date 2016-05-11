<?php
if ($authkey!='boxshop94124') {die();};

class Alarm {
	public function getstatus(){//see what the database says the status is, and if it hasn't been updated in awhile, set the status to the "connection failed" state
		global $database;		
		$timeout=60*61;
		$q="SELECT timestamp FROM lastcontact WHERE ID = 1";
		$row=@mysql_fetch_array($database->query($q));		
		$q="SELECT ID, timestamp, state FROM alarmevents WHERE componentID = 'M' ORDER BY timestamp DESC LIMIT 1;";
		$row2=@mysql_fetch_array($database->query($q));
		if ($row['timestamp'] < (time()-$timeout)){
			if ($row2['state']!= '6') {
				$q="INSERT INTO alarmevents(componentID, state, timestamp, UID) VALUES ('M', '6', " . time() . ", -1);";				
				$database->query($q);
			}
			return 6;						
		}
		else {	
			$state = (int) $row2['state'];
			return $state;
		}
	}
	public function doStatusLog() {//called mostly by the alarm api to parse and log any changes in the status
		global $database;
		$inputVars = array('M', 'D', 'E', 'G', 'H', 'B', 'W');
		$keyPadCodes = array('D'=>'K', 'E'=>'L');
		$json = file_get_contents('php://input');
		$data = json_decode($json, true);
		
		foreach ($inputVars as $i => $value){
			if (array_key_exists($value, $data)){
				$q="SELECT ID, timestamp, state FROM alarmevents WHERE componentID = '$value' ORDER BY timestamp DESC LIMIT 1;";
				$row=@mysql_fetch_array($database->query($q));
				if ($data[$value] != $row['state']){
					$uid = -1;
					$extra = '';
					if ($value == 'D' or $value =='E'){
						if ($data['U'] > 0){
							$uid = $data['U'];
						}
						if ($data[$keyPadCodes[$value]]){
							$extra .= $data[$keyPadCodes[$value]];
						}
					}
						
					$q="INSERT INTO alarmevents(componentID, state, timestamp, UID, extra) VALUES ('$value', $data[$value], " . time() . ", $uid, '$extra');";
					$database->query($q);
				}
			}
		}
		$q="UPDATE lastcontact SET timestamp = " . time() . " WHERE ID = 1";
		$database->query($q);
	}
	public function clearExpiredActions(){
		global $database;
		$result=$database->query("SELECT ID, expiration FROM pendingactions WHERE state > 0");
		if (mysql_num_rows($result)){
			while ($row = mysql_fetch_array($result)){
				if ($row['expiration'] < time()){
					$q = 'UPDATE pendingactions SET state = 0 WHERE ID = ' . $row['ID'];
					$database->query($q);
				}
			}
		}
		$q = "DELETE FROM codes WHERE startDate > 0 AND endDate < " . time() . ';';
		$database->query($q);
	}
}
class Guestcodes {
	public function validateAndConvert($codein, $uid=null){//validates codes, both from users entering new temporary ones and from the alarm api to make sure they're numbers
		global $database;
		$errDescrips=array(0=>'', 1=>'* The code must be 5 or more digits long', 2=>'* The code must contain only numbers or letters', 3=>'* That code\'s being used by somebody else already', 4=>'* That code is too easy to guess and is not allowed');

		$num=array('a'=>'2', 'b'=>'2', 'c'=>'2', 'd'=>'3', 'e'=>'3', 'f'=>'3', 'g'=>'4', 'h'=>'4', 'i'=>'4', 'j'=>'5', 'k'=>'5', 'l'=>'5', 'm'=>'6', 'n'=>'6', 'o'=>'6', 'p'=>'7', 'q'=>'7', 'r'=>'7', 's'=>'7', 't'=>'8', 'u'=>'8', 'w'=>'8', 'x'=>'9', 'y'=>'9', 'z'=>'9');
		$len=strlen($codein);
		$err=0;
		if ($len<5){$err=1;}
		$chars=str_split($codein);
		$output='';
		foreach($chars as $k=>$char){
			if (preg_match('/[0-9]/',$char)){
				$output.=$char;
			}
			else if(preg_match('/[A-z]/',$char)){
				$output.=$num[strtolower($char)];
			}
			else {
				if ($k==($len-1) && $char=='#'){
					if ($len < 6){
						$err=1;
					}
				}
				else {
					$err=2;
				}
			}
		}
		$code=$output;
		if ($uid){
			$q="SELECT ID FROM codes WHERE code=$code AND UID != $uid;";
			$result=$database->query($q);
			if (@mysql_fetch_array($result)){
				$err=3;
			}
		}
		$q="SELECT ID FROM prohibitedcodes WHERE code=$code;";
		$result=$database->query($q);
		if (@mysql_fetch_array($result)){
			$err=4;
		}
		
		return array('err'=>$err, 'code'=>$output, 'errDescrip'=>$errDescrips[$err]);
	}
	public function startOfToday(){		
		$now=time();
		return mktime(0,0,0, date('m',$now),date('d',$now), date('Y',$now));
	}
	public function doCodeUpdate($codein, $uid=null){
		global $database, $form;
		$q = "SELECT ID, code FROM codes WHERE UID = $uid AND startDate = 0";
		$result = $database->query($q);
		$row = @mysql_fetch_array($result);
		if ($row){
			if ($row['code'] == $codein){
				return 1;
			}
			else {
				$mode = 1;
			}
		}
		else {
			$mode = 2;
		}
		if ($mode){
			$codeResult = $this->validateAndConvert($codein, $uid);
			if ($codeResult['err'] > 0){
				$form->setError('userCode', $codeResult['errDescrip']);
				return 0;
			}
			else {
				if ($mode == 1){
					$q = "UPDATE codes SET code = '" . $codeResult['code'] . "' WHERE ID = " . $row['ID'] . ';';
				}
				else {
					$q = "INSERT INTO codes (UID, startDate, notes, code, keypadK, keypadL) VALUES ($uid, 0, '', '" . $codeResult['code'] . "', 1, 0);";
				}
			}
			$database->query($q);
			$this->updateCodeHash();
			return 1;
		}
				
	}
	public function updateCodeHash(){
		global $database;
		$compiledCodes = ($this->compileCodeJson());
		$codeHash = hash('sha256', $compiledCodes);
		$q = "UPDATE settings SET data = '" . $codeHash . "' WHERE setting = 'currentCodeHash';";
		$database->query($q);
	}
    
	public function getGuestCodes($uid){
		global $database;
		$q="SELECT codes.ID, codes.UID, codes.startDate, codes.notes, codes.code, users.username FROM codes INNER JOIN users ON codes.UID = users.UID WHERE codes.UID = " .  $uid . " AND codes.endDate >= " . $this->startOfToday() . " ORDER BY codes.startDate ASC;";
		$result=$database->query($q);
		return $result;
	}
	public function getPermCodes($uid){
		global $database;
		$output = array('code'=>'No code Set', 'accessZones'=>array());
		$zoneNames = array('keyPadK'=>'Front Door', 'keyPadL'=>'CNC', 'keyPadM'=>'Machineshop');
		$q="SELECT code, keyPadK, keyPadL, keyPadM FROM codes WHERE UID = " .  $uid . " AND codes.startDate = 0";
		$result=$database->query($q);
		$row=@mysql_fetch_array($result);
		if ($row){
			$output['code'] = $row['code'];
			$accessZones = array();
			foreach($zoneNames as $k=>$v){
				if($row[$k] == 1){
					$accessZones[] = $zoneNames[$k];
				}
			}
			$output['accessZones'] = $accessZones;
		}
		return $output;
	}
	public function compileCodeJson(){
		global $database;
		$result=$database->query("SELECT * FROM codes");
		$outputData = array();
		$keyPadCodes = array('K', 'L', 'M');
		if (mysql_num_rows($result)){
			while ($row = mysql_fetch_array($result)){
				$keypads = array();
				foreach($keyPadCodes as $k=>$keypadId){
					if ($row['keyPad' . $keypadId] == 1){
						$keypads[] = $keypadId;
					}
				}
				$outputRow = array('code'=>$row['code'], 'user'=>$row['UID'], 'startDate'=>$row['startDate'], 'endDate'=>$row['endDate'], 'keypads'=>$keypads);
				$outputData[] = $outputRow;
			}
		}
		return json_encode($outputData);
	}

}