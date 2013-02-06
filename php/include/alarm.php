<?php
if ($authkey!='') {die();};

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
		$inputVars = array('M', 'D', 'G', 'B', 'W', 'K');
		foreach ($inputVars as $i => $value){
			if (!is_null($_GET[$value])){
				$q="SELECT ID, timestamp, state FROM alarmevents WHERE componentID = '$value' ORDER BY timestamp DESC LIMIT 1;";	
				$row=@mysql_fetch_array($database->query($q));
				if ($_GET[$value] != $row['state']){
					$q="INSERT INTO alarmevents(componentID, state, timestamp, UID) VALUES ('$value', $_GET[$value], " . time() . ", -1);";
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
	}
}
class Guestcodes {
	public function doCodeCheckAndLog($code){//called by the alarm api to log codes entered at the keypad
		global $database;
		$goodcode = 0;
		$q="SELECT ID, UID, code, firstused FROM codes WHERE codeDate = " . $this->startOfToday() . ";";
		$codeToCheck = str_split($code);
		$result = $database->query($q);
		$row=@mysql_fetch_array($result);
		while ($row['code'] && $goodcode == 0){	
			$codeToCheckAgainst = str_split($row['code']);
			$passcharcount = 0;
    		$i=0;
    		while($i < count($codeToCheck)) {
      			if ($codeToCheckAgainst[$passcharcount] == $codeToCheck[$i]) {//matched this character, go to next one
        			$passcharcount++;
    			}
 	     		$i++;
  			}
	    	if ($passcharcount == count($codeToCheckAgainst)){
	      		$goodcode = 1;
	      		$code = $row['code'];
				if ($result['firstused']){
					$q="UPDATE codes SET uses=uses+1 WHERE ID=" . $row['ID'] . ";";
				}
				else {
					$q="UPDATE codes  SET uses=uses+1, firstused=" . time() . " WHERE ID=" . $row['ID'] . ";";
				}
				$database->query($q);
				$uid=$row['UID'];
			}
			$row=@mysql_fetch_array($result);
		}
		$q="INSERT INTO alarmevents(componentID, state, timestamp, extra, UID) VALUES ('K', $goodcode, " . time() . ", $code, $uid);";
		$database->query($q);
		return $goodcode;
	}
	public function validateAndConvert($codein, $uid=null){//validates codes, both from users entering new temporary ones and from the alarm api to make sure they're numbers
		global $database;
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
			$q="SELECT ID FROM codes WHERE code=$code AND codeDate >= " . $this->startOfToday() . " AND UID != $uid;";
			$result=$database->query($q);
			if (@mysql_fetch_array($result)){
				$err=3;
			}
		}
		return array('err'=>$err, 'code'=>$output);
	}
	public function startOfToday(){		
		$now=time() - 3600;
		return mktime(0,0,0, date(m,$now),date(d,$now), date(Y,$now));
	}

}