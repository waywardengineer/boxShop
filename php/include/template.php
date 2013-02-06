<?php
if ($authkey!='') {die();};

class Template {
	private $values = array();
	
    public function __construct($file) {
		$this->file=$file;
		if (!file_exists($this->file)) {
	        return "Error loading template file ($this->file).<br />";
		}
		$this->output = file_get_contents($this->file);
    }
		
		
	public function set($key, $value) {
		$this->values[$key] = $value;
	}
	
	
	
	public function makeLinkBars($links, $value){
		$output='';
		if ($links){
			foreach ($links as $linkname => $link){
				if (substr($link,0,3)=='JS:'){
					$href='href="#" onClick="' . substr($link,3) .'"';
				}
				else {
					$href='href="' . $link .'"';
				}
				$output.='<a ' . $href .' class="toplinks">' . $linkname . '</a>';
			}
		}
		$this->values[$value] = $output;
	}
	
	
	
	public function makeGuestCodesList($result){
		$columnWidths=array(40, 120, 80, 120, 70, 170);
		global $user;
		$codes=array();
		$output = '<h3>Your existing codes:</h3>' . $this->makeListRow(array('', 'Username', 'Code', 'Date', 'Used', 'Notes'), $columnWidths, 1);
		while ($row=@mysql_fetch_array($result)){
			if ($row['UID']==$user->uid){
				$username='You';
			}
			else {
				$username=$row['username'];
			}
			$used = $row['uses']?'Yes':'No';
			$formhtml='<form name="deleteCode" action="proccode.php" method="post"><input type="hidden" name="doWhat" value="delete"><input type="hidden" name="deleteWhat" value="' . $row['ID'] . '"><input type="submit" class="submitbtn" value="X"></form>';
			$output.= $this->makeListRow(array($formhtml, $username, $row['code'], date('M j, Y', $row['codeDate']), $used, $row['notes']), $columnWidths, 0);
		}
		$this->values['codes'] = $output;									   
	}
	
	public function makeEventLog($result){
		global $guestcodes;
		
		$columnWidths=array(140, 250, 110);
		$output = '<div class="eventlogbox" id="eventlog_hdr" style="height:15px;" >';
		$output .=  $this->makeListRow(array('Component', 'Description', 'Time'), $columnWidths, 1, 'eventlog_col', '<div class="eventlog_header">', '</div>');
		$output .= '</div><div class="eventlogbox" id="eventlog">';
		$today = $guestcodes->startOfToday();
		$yesterday = $today-(24*3600);
		while ($row=@mysql_fetch_array($result)){
			$eventTime = $row['timestamp'] - 3600;
			if($eventTime >= $today){$time=date('\T\o\d\a\y g:ia', $eventTime);}
			else if($row['timestamp'] >= $yesterday){$time=date('\Y\e\s\t\e\r\d\a\y g:ia', $eventTime);}
			else {$time=date('M j g:ia', $eventTime);}
			$output.=$this->makeListRow(array($row['component'], $row['description'], $time), $columnWidths, 0, 'eventlog_col', '<div class="eventlog_' . $row['type'] . $row['state'] . '">', '</div>');
		}
		$output .= '</div>';
		$this->values['eventlog'] = $output;									   
	}
	public function makeUserList($result) {
		$columnWidths=array(120, 200, 120, 170);
		$ulevelNames = array(1 =>'New User', 2=>'Trusted', 9=>'Admin');	
		$output = $this->makeListRow(array('Username', 'Email', 'Last Online', 'User Level'), $columnWidths, 1);
		while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
			$uid = $row["UID"];
			$ulevel = $row["userlevel"];
			if ($row["timestamp"]<100){
				$time='Never';
			}
			else{
				$time=date('M j, Y', $row["timestamp"]);
			}
			$output .='<form method="post" action="adminprocess.php?subuser=' . $uid . '" id="adminForm' . $uid . '">';
			$selecthtml= '<select name="level' . $uid . '" id="level' . $uid . '" onChange=\'changed(' . $uid . ', "' . $row["username"] . '")\'> 
					<option value="1"';
					if ($ulevel==1) {$selecthtml .= ' selected="selected" class="admin_selected"';}
					$selecthtml.='>New User</option><option value="2"';
					if ($ulevel==2) {$selecthtml .= ' selected="selected" class="admin_selected"';}
					$selecthtml .= '>Trusted</option><option value="9"';
					if ($ulevel==9) {$selecthtml .= ' selected="selected" class="admin_selected"';}
					$selecthtml .= '>Admin</option><option value="-1">Delete</option></select>';
			$output .=$this->makeListRow(array($row["username"], $row["email"], $time, $selecthtml), $columnWidths, 0) . '</form>';
		}
		$this->values['users'] = $output;
	}
	
	
	public function makestatus($alarmstatus, $userloggedin){
		if ($userloggedin){
			$descriptions= array(1 => 'Not Armed', 2 => 'Armed', 3 => 'Waiting to Arm', 4 => 'Alarm',  5 => 'Resetting from alarm', 6 => 'Unable to connect to alarm');
			$styles = array (1=>'green', 2=> 'yellow', 3=>'yellow', 4=>'red', 5=>'yellow', 6=>'red');
		}
		else {
			$descriptions= array(1 => 'OK', 2 => 'OK', 3 => 'OK', 4 => 'Alarm', 5 => 'Resetting from alarm', 6 => 'Unable to connect to alarm');
			$styles = array (1=>'green', 2=> 'green', 3=>'green', 4=>'red', 5=>'yellow', 6=>'red');
		}
		$output='Alarm Status:<span class="' . $styles[$alarmstatus] . '">' . $descriptions[$alarmstatus] . '</span>';
		$this->values['alarmstatus'] = $output;		
	}
	
	public function makeButton($state, $type){
		$btnTxt = array(1 => 'Buzz Door Open', 2=> 'Stop Alarm');
		$replacementTxt = array(1 => 'Tell person at door to press * to get in', 2=> 'Alarm stop requested, may take up to 30 seconds');
		if ($state == 0){
			$output = '<div class="userButton"><form action="index.php" method="post"><input type="submit" class="submitbtn" value="' . $btnTxt[$type] . '"><input type = "hidden" name="actionType" value="' . $type . '"></form></div>';
		}
		else {
			$output = '<div class="userButton"><span>' . $replacementTxt[$type] . '</span></div>';
		}
		$valuename = 'button' . $type;
		$this->values[$valuename] = $output;
	}
	
	
	public function doOutput($showsections) {
		$sections=array('login', 'page_admin', 'page_admin2', 'page_forgotpass', 'page_login', 'page_codes', 'page_index', 'regform', 'calscripts');
		foreach($sections as $section){//hide sections we won't use
			if (!in_array($section, $showsections)){
				$matchstring='/{' . $section . '.*\/' . $section . '}/s';
				$this->output=preg_replace($matchstring,'',$this->output);
			}
		}
		foreach ($this->values as $key => $value) {
			$matchstring = '{' . $key . '}';
			$this->output = str_replace($matchstring, $value, $this->output);
		}
		$this->output=preg_replace('/{.*}/','',$this->output);
		return $this->output;
	}
	
	
	private function makeListRow($cols, $widths, $isHeading, $class="listcolumn", $before="<p>", $after="</p>"){
		$output='';
		if ($isHeading){
			$headingtag='<strong>';
			$headingclosetag='</strong>';
		}
		foreach($cols as $k=>$col){
			$output.='<div class="' . $class . '" style="width:' . $widths[$k] . 'px">' . $headingtag . $col . $headingclosetag .  '</div>';
		}
		$output=$before . $output . $after;
		return $output;
	}
	
}
?>