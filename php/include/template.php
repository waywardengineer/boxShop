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
		$columnWidths=array(40, 120, 80, 120, 170);
		global $user;
		$codes=array();
		$output = '<h3>Your existing codes:</h3>' . $this->makeListRow(array('', 'Username', 'Code', 'Date', 'Notes'), $columnWidths, 1);
		while ($row=@mysql_fetch_array($result)){
			if ($row['UID']==$user->uid){
				$username='You';
			}
			else {
				$username=$row['username'];
			}
			$formhtml='<form name="deleteCode" action="proccode.php" method="post"><input type="hidden" name="doWhat" value="delete"><input type="hidden" name="deleteWhat" value="' . $row['ID'] . '"><input type="submit" class="submitbtn" value="X"></form>';
			$output.= $this->makeListRow(array($formhtml, $username, $row['code'], date('M j, Y', $row['startDate']), $row['notes']), $columnWidths, 0);
		}
		$this->values['codes'] = $output;									   
	}
	
	public function makeEventLog($result, $isAdmin, $listOnly = false){
		global $guestcodes;
		$columnWidths=array(140, 250, 110);
		$columnTitles=array('Component', 'Description', 'Time');
		if ($isAdmin){
			$columnWidths[] = 100;
			$columnTitles[] = 'User';
		}
		if (!$listOnly){
			$output = '<div class="infobox_header">';
			$output .=  $this->makeListRow($columnTitles, $columnWidths, 1, 'eventlog_col', '<div>', '</div>');
			$output .= '</div><div class="infobox_info" id="eventlog">';
		}
		else {
			$output = '';
		}
		$today = $guestcodes->startOfToday();
		$yesterday = $today-(24*3600);
		while ($row=@mysql_fetch_array($result)){
			$eventTime = $row['timestamp'];
			if($eventTime >= $today){$time=date('\T\o\d\a\y g:ia', $eventTime);}
			else if($row['timestamp'] >= $yesterday){$time=date('\Y\e\s\t\e\r\d\a\y g:ia', $eventTime);}
			else {$time=date('M j g:ia', $eventTime);}
			$arr = array($row['component'], $row['description'], $time);
			if ($isAdmin){
				$arr[] = $row['username'];
			}
			
			$output.=$this->makeListRow($arr, $columnWidths, 0, 'eventlog_col', '<div class="eventlog_' . $row['type'] . $row['state'] . '">', '</div>');
		}
		if (!$listOnly){
			$output .= '</div>';
		}
		$this->values['eventlog'] = $output;									   
	}
	public function makeUserList($result) {
		$columnWidths=array(120, 200, 120, 170, 120, 120);
		$ulevelNames = array(1 =>'New User', 2=>'Trusted', 9=>'Admin');	
		$output = $this->makeListRow(array('Username', 'Email', 'Last Online', 'User Level', 'Front Door', 'MachineShop'), $columnWidths, 1);
		while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
			$uid = $row["UID"];
			$ulevel = $row["userlevel"];
			if ($row["timestamp"]<100){
				$time='Never';
			}
			else{
				$time=date('M j, Y', $row["timestamp"]);
			}
			$output .='<form method="post" action="adminprocess.php?subuser=' . $uid . '" id="adminForm' . $uid . '"><input type="hidden" name="action' . $uid . '">';
			$selecthtml= '<select name="level' . $uid . '" id="level' . $uid . '" onChange=\'changeLevel(' . $uid . ', "' . $row["username"] . '")\'> 
					<option value="1"';
					if ($ulevel==1) {$selecthtml .= ' selected="selected" class="admin_selected"';}
					$selecthtml.='>New User</option><option value="2"';
					if ($ulevel==2) {$selecthtml .= ' selected="selected" class="admin_selected"';}
					$selecthtml .= '>Trusted</option><option value="9"';
					if ($ulevel==9) {$selecthtml .= ' selected="selected" class="admin_selected"';}
					$selecthtml .= '>Admin</option><option value="-1">Delete</option></select>';
			if ($row['code']){
				$machineDoorHtml = '<input type="checkbox" name="machineDoor' . $uid . '"';
				if ($row['keyPadL'] == 1) {
					$machineDoorHtml .= ' checked="checked"';
				}
				$machineDoorHtml .='" onChange=\'changeDoor(' . $uid . ')\'>';
				$frontDoorHtml = '<input type="checkbox" name="frontDoor' . $uid . '"';
				if ($row['keyPadK'] == 1) {
					$frontDoorHtml .= ' checked="checked"';
				}
				$frontDoorHtml .='" onChange=\'changeDoor(' . $uid . ')\'>';

			}
			else {
				$machineDoorHtml = '';
				$frontDoorHtml  = '';
			}
			$output .=$this->makeListRow(array($row["username"], $row["email"], $time, $selecthtml, $frontDoorHtml, $machineDoorHtml), $columnWidths, 0) . '</form>';
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
		$sections=array('trusted', 'login', 'page_admin', 'page_admin2', 'page_forgotpass', 'page_login', 'page_codes', 'page_edit', 'page_index', 'page_index2', 'regform', 'regcodeform', 'calscripts');
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
	public function makeBarGraph($values, $maxHeight, $labelSkip, $title){
		$totalWidth=800;
		$spacing=1;
		$totalHeight=180;
		$labelCount = 1;
		$barWidth=intval($totalWidth/count($values))-$spacing;
		$output='<div class="infobox_header"><strong>' . $title . '</strong></div><div class="infobox_info">';
		$left=25;
		$sideWaysTxtLimit = 30;
		if ($barWidth < $sideWaysTxtLimit){
			$textClass = 'barGraphBarSideways';
		}
		else {
			$textClass = 'barGraphBar';
		}
		$labelOffset = -23 + intval($barWidth/2);
		
		foreach($values as $value){
			$height = intval(($totalHeight * $value[1])/$maxHeight);
			$top = $totalHeight - $height + 15;
			if ($value[1] > 0 && (($height > 20 and $barWidth > $sideWaysTxtLimit) or $height > 40)){
				$text = $value[1];
			}
			else {
				$text = '&nbsp;';
			}
			$output .= '<div class="' . $textClass . '" style="height:' . $height . 'px; width:' . $barWidth . 'px; top:' . $top . 'px; left:' . $left . 'px"><span>' . $text . '</span></div>';
			if ($labelCount == 1){
				$output .= '<span class="barGraphLabel" style="left:' . ($left+$labelOffset) . 'px;">' . $value[0] . '</span>';
			}
			$labelCount = ($labelCount >= $labelSkip)?1:$labelCount + 1;
			$left += ($barWidth + $spacing);

		}
		$output .= '</div>';
		return $output;
	}
	
}
?>