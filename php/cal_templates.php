<?php
class eventTemplate {

    protected $values = array();
    public function __construct($templateID) {
		$templates = array();

	
		$templates['mainEvent']='
				<a id="closeCalBox" onclick="closeEvents()">Close</a>
			<div class="caleventinnerbox">
				<p class="headline"><strong>{summary}</strong></p>
				<p><strong>{date}</strong></p>
				<p>{description}</p>
			</div>';
	
		$templates['sidebar']='

						<div class="cal_catlocation_menu"><strong>Categories</strong><br />
							<div style="height:{categoryheight}px;">
							
							<a class="calMenu calMenuSelected" id="categoryall" onclick="changeCategory(\'all\')">All Categories<br /></a>
							{categories}
							</div>
							</div>
						<div class="cal_catlocation_menu"><strong>Locations</strong><br />
							<div style="height:{locationheight}px;">
							<a class="calMenu calMenuSelected" id="locationall" onclick="changeLocation(\'all\')">All Locations<br /></a>
							{locations}
							</div>							</div>
';
	
	
		$templates['categoryListing']='';
		$templates['locationListing']='';
			
		$templates['calendar']='
				
			<div class="calheader">
			<a style="font-size:12px;" onclick="{showlist}">(View as List)</a></p>
			<span style="display:block; width:546px; text-align:center;">
			<a id="prev" onclick="{prevaction}" style="float:left;">Previous Month</a><strong>{thismonth}</strong>
			<a id="nextmonth" onclick="{nextaction}" style="float:right; margin-right:10px;">Next Month</a></span></div>
			<div class="mainCalendar" style="height:{height}px; width:{width}px;">
				{calendar}
			</div>';
		$templates['calendarEventSingle']='
			{summary}
			<span class="calendarEventMouseoverDesc">{description}<br />&nbsp;<br />
			<strong>Time</strong>: {date}<br />
			<br />
				Last edited by: {addedby} {editlink}
			</span>';
		$templates['calendarEventMulti']='
			<a style="height:{height}px;" >{summary}</a>
			<span class="calendarEventMouseoverDesc" style="border-bottom-style:solid; border-bottom-color:#000; border-bottom-width:1px;">
				{description}<br />&nbsp;<br />
				<strong>Time</strong>: {date}<br />
				
				Last edited by: {addedby} {editlink}
			</span>';
		$templates['eventlist']='
			<div style="height:600px; overflow:auto;">
				<div class="calheader">
					<a style="font-size:12px;" onclick="{showlist}">(View as Calendar)</a></p>
				</div>
				{eventlist}
			</div>
			';
		$templates['eventlistevent']='
			<span class="eventListHeadline">{summary}</span>
			
			<strong>{date}</strong><br />
			
			<p>{description}</p>
			Last edited by: {addedby} {editlink}

			<span style="display:block; height:60px;"></span>';

	$this->template = $templates[$templateID];
    }
	public function calendarGetHTML($bitID, $contents='&nbsp;', $style=null){
		$HTMLbits=array(
						'notDay'=>'<div class="calendarNotDay" {style}>{contents}</div>',
						'eventDay' => '<div class="calendarEventDay" {style}>{contents}</div>',
						'Day'=>'<div class="calendarDay" {style}>{contents}</div>',
						'dayHeading'=>'<div class="calendarDayHeading" {style}>{contents}</div>',
						'weekDivider'=>'<br />
						');
		$output=str_replace('{contents}',$contents,$HTMLbits[$bitID]);
		$output=str_replace('{style}',$style,$output);
		return $output;
	}	
		
	public function set($key, $value) {
		$this->values[$key] = $value;
	}
	 
	public function doOutput() {
		$output = $this->template;
		foreach ($this->values as $key => $value) {
			$tagToReplace = '{' . $key . '}';
			$output = str_replace($tagToReplace, $value, $output);
		}
		return $output;
	}
	
}
?>