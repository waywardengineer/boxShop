<?php
if ($auth!='auth') {die();};

class Template {
	protected $values = array();
	
    public function __construct($file, $subTemplateFile = false) {
		$this->file=$file;
		if (!file_exists($this->file)) {
	        return "Error loading template file ($this->file).<br />";
		}
		if ($subTemplateFile && !file_exists($subTemplateFile)) {
	        return "Error loading template file ($subTemplateFile).<br />";
		}
		$this->output = file_get_contents($this->file);
		if ($subTemplateFile){
			$subTemplate = file_get_contents($subTemplateFile);
			$subTemplateSections = array('scripts', 'content');
			foreach($subTemplateSections as $sectionName){
				$reResults = array();
				$reString = '/({' . $sectionName . '})(.*)({\/' . $sectionName . '})/s';
				preg_match($reString, $subTemplate, $reResults);
				//$this->values[$sectionName] = $reResults[2];
				$matchstring = '{' . $sectionName . '}';
				$this->output = str_replace($matchstring, $reResults[2], $this->output);

			}

		}
			
    }
	public function set($key, $value) {
		$this->values[$key] = $value;
	}
	public function setMulti($array){
		foreach($array as $k=>$v){
			$this->set($k, $v);
		}
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
		return $output;									   
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
					$selecthtml .= '>Trusted</option><option value="3"';
					if ($ulevel==3) {$selecthtml .= ' selected="selected" class="admin_selected"';}
					$selecthtml .='>Group</option><option value="9"';
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
	
	
	public function doOutput($showSections = array()) {
		$reResults = array();
		while(preg_match('/{\/(.*)}/', $this->output, $reResults)){
			if (!in_array($reResults[1], $showSections)){
				$matchstring='/{' . $reResults[1] . '.*\/' . $reResults[1] . '}/s';
			}
			else {
				$matchstring='#{/' . $reResults[1] . '}#';
			}
			$this->output=preg_replace($matchstring,'',$this->output);

		}
		foreach ($this->values as $key => $value) {
			$matchstring = '{' . $key . '}';
			$this->output = str_replace($matchstring, $value, $this->output);
		}
		$this->output=preg_replace('/{[A-Za-z]*}/','',$this->output);
		return $this->output;
	}
		
	
	public function makeListRow($cols, $widths, $isHeading, $class="listcolumn", $before="<p>", $after="</p>"){
		$output='';
		if ($isHeading){
			$headingtag='<strong>';
			$headingclosetag='</strong>';
		}
		else {
			$headingtag='';
			$headingclosetag='';
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
	function makeNav($array, $secondLevel = false){
		$output = $secondLevel?'<ul>':'<ul id="nav">';
		foreach($array as $k=>$v){
			$output .='<li>';
			if (is_array($v)){
				$output .= '<a href="' . reset($v) . '">' . $k . '</a>' . $this->makeNav($v, true);
			}
			else {
				$output .= '<a href="' . $v . '">' . $k . '</a>';
			}
			$output .='</li>';
		}
		$output .= '</ul>';
		return $output;
	}
				
	public function makeTable($data, $caption = false, $hasColHeadings = true, $hasRowHeadings = true, $id = false){

		$output = "<table";
		$output .= $id?" id = '$id'>":'>';
		$output .=  $caption?"<caption>$caption</caption>":'';
			
		foreach($data as $rowIndex=>$row){
			if ($rowIndex == 0 && $hasColHeadings){
				$output .= '<thead><tr>';
				foreach($row as $colIndex=>$col){
					if ($colIndex == 0 && $hasRowHeadings){
						$output .= '<td></td>';
					}
					else {
						$output .= "<th scope='col'>$col</th>";
					}
				}
			}
			else{
				if ($rowIndex == 0 && !$hasColHeadings){
					$output .= '<tbody>';
				}
				if ($rowIndex == 1 && $hasColHeadings){			
					$output .= '</thead><tbody>';
				}
				$output .= '<tr>';
				foreach($row as $colIndex=>$col){
					if ($colIndex == 0 && $hasRowHeadings){
						$output .= "<th scope='row'>$col</th>";
					}
					else {
						$output .= "<td>$col</td>";
					}
				}
			}
			$output .='</tr>';
		}
		$output .='</tbody></table>';

		return $output;
	}
	public function makeFormOptions($array){
		$output = '';
		foreach($array as $k=>$v){
			$output .= '<option value="' . $k;
			$output .= '">' . $v . '</option>';
		}
		return $output;
	}
	function createNav(){
		global $user, $database;
		$navItems = array();
		if ($user->logged_in){
			if ($user->isTrusted()){
				$navItems['Door Codes'] = 'codes.php';
				$navItems['CNC logs'] = 'cnc.php';
				$navItems['Shop Supplies Management'] = array('Supplies Management'=>'supplies.php', 'Logs'=>'supplieslog.php');
				$navItems['Wiki'] = array();
				$navItems['Event Calendar'] = 'calendar.php';
				$navItems['Door Logs'] = array('Event Log'=>'logs.php?log=event', '24 hr graph'=>'logs.php?log=day', 'Week graph'=>'logs.php?log=week', 'Year graph'=>'logs.php?log=year');
				$result = $database->query('SELECT DISTINCT page_name FROM wiki_sections');
				while ($row = @mysql_fetch_array($result)){
					$navItems['Wiki'][$row['page_name']] = 'wiki.php?l=' . $row['page_name'];
				}
				$navItems['Wiki']['New Page'] = "addwikipage.php";
			}
			if ($user->isAdmin()){
				$navItems['Admin'] = 'admin.php';
			}
			$navItems['logout'] = 'process.php';
		}
		else {
			$navItems['register'] = 'register.php';
		}
		$this->set("nav", $this->makeNav($navItems));
	return;
	}

	
}
?>