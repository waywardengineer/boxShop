<?php
/*
Plugin Name: ERS Events
Version: 0.1
Description: Customized version of <a href="http://dev.webadmin.ufl.edu/~dwc/">ical-events</a>

*/
define('AUTH', 'YFNA');

require_once('cal_templates.php');

$authkey='boxshop94124';
include("include/common.php");

if (! defined('ICAL_EVENTS_DEBUG')) define('ICAL_EVENTS_DEBUG', false);

// As defined by import_ical.php


class events {
	public $allevents;//all the events
	public $events; //the events with whatever additional constraints have been applied
	public $allcategories;
	public $categories;
	public $alllocations;
	public $locations;
	public function __construct($sqlparams=null){
		$querystring="SELECT cal_events.ID AS UID, cal_events.starttime AS StartTime, cal_events.endtime AS EndTime, cal_events.title AS Summary, cal_events.description AS Description, cal_events.addedby AS addedBy, 0 AS LocationID, 0 AS CategoryID, '' AS Location, '' AS Category
				FROM cal_events
				";
		$result=@mysql_query($querystring);
		$this->allevents=array();
		while ($row=@mysql_fetch_array($result)){
			$row['Description'] =preg_replace('#(http://www.|http://|www.)([A-Za-z0-9/\-.\?&=\#!;:_%]*)#', '<a href="http://www.\2" target="_blank">\1\2</a>', $row['Description']); 
			$this->allevents[]=$row;
		}
		if (! $this->allevents) {
			return;
		}
		$this->allevents=$this->sort_by_key($this->allevents, 'StartTime');
		$this->events=$this->allevents;
		$this->doConstrainedCategories();
		$this->allcategories=$this->categories;
		$this->alllocations=$this->locations;
	}
	public function do_display_events($date_format, $replace_newlines_with, $eventTemplateID, $showEdit=false) {
		global $user;

		$i=0;

		$output = '';
		$uids='';			
		foreach ($this->events as $event) {
			if (!(!$event['Summary'] && !$event['Description'])){
					$date_txt = htmlentities(date($date_format, $event['StartTime']));
				if ($event['Summary']) {
					$summary_txt = $event['Summary'];
				}
				else {
					$summary_txt='No event Summary';
				}
				if ($event['addedBy']) {
					$url_txt=$event['addedBy'];

				}
				if ($event['Description']) {
					$description_txt = $event['Description'];
				}
				else {
					$description_txt = 'No further description available';
				}
				$description_txt_len = strlen($description_txt);


				if ($event['UID']) {
					$uid_txt=$event['UID'];
				}
				else {
					$uid_txt=$i;
					$i++;
				}
				if ($user->isTrusted()){
					$editlink = '<a href="calendar.php?action=edit&id=' . $event['UID'] . '">(edit)</a>';
				}
				else {
					$editlink = '';
				}

				$calEventHtml = new eventTemplate($eventTemplateID);
				$calEventHtml->set('date', $date_txt);
				$calEventHtml->set('summary', $summary_txt);
				$calEventHtml->set('description', $description_txt);
				$calEventHtml->set('uid', $uid_txt);
				$calEventHtml->set('location', $event['Location']);
				$calEventHtml->set('descriptionLength', $description_txt_len);					
				$calEventHtml->set('addedby', $event['addedBy']);					
				$calEventHtml->set('editlink', $editlink);					
				$output.=$calEventHtml->doOutput();
			}


		}
		
		return $output;
	}
	private function doConstrainedCategories(){
		$categories=array();
		$locations=array();
		foreach ($this->events as $event){
			if (!array_key_exists($event['CategoryID'], $categories)){
				$categories[$event['CategoryID']]=$event['Category'];
			}
			if (!array_key_exists($event['LocationID'], $locations)){
				$locations[$event['LocationID']]=$event['Location'];
			}
		}
		$this->categories=$categories;
		$this->locations=$locations;
	}
			
	public function constrainToLocation($location){
		$output=array();
		foreach($this->events as $event){
			if ($event['LocationID'] == $location){
				$output[]=$event;
			}
		}
		$this->events=$output;
		$this->doConstrainedCategories();
		
	}
	public function constrainToUID($UID){
		$output=array();
		foreach($this->events as $event){
			if ($event['UID'] == $UID){
				$output[]=$event;
			}
		}
		$this->events=$output;
		$this->doConstrainedCategories();
	}


	public function constrainToCat($cat){
		$output=array();
		foreach($this->events as $event){
			if ($event['CategoryID'] == $cat){
				$output[]=$event;
			}
		}
		$this->events=$output;
		$this->doConstrainedCategories();
	}
	public function constrainToCount($count, $start=0){
		$constrained = array();
		$i=0;
		$limit = $count + $start;
		foreach($events as $event){
			if ($i >= $start and $i < $limit){
				$constrained[]=$event;
			}
		}
		$this->events=$constrained;
		$this->doConstrainedCategories();
	}
	public function constrainToDate($gmt_start = null, $gmt_end = null) {
		$constrained = array();
		$count = 0;
		foreach ($this->events as $event) {
			if ($this->falls_between($event, $gmt_start, $gmt_end)) {
				$constrained[] = $event;
			}
		}
		$this->events = $constrained;
		$this->doConstrainedCategories();
	}
	public function resetConstraints(){
		$this->events=$this->allevents;
		$this->categories=$this->allcategories;
		$this->locations=$this->alllocations;
	}
			


	/*
	 * Sort the specified associative array by the specified key.
	 * Originally from
	 * http://us2.php.net/manual/en/function.usort.php.
	 */
	private function sort_by_key($data, $key) {
		// Reverse sort
		$compare = create_function('$a, $b', 'if ($a["' . $key . '"] == $b["' . $key . '"]) { return 0; } else { return ($a["' . $key . '"] < $b["' . $key . '"]) ? -1 : 1; }');
		usort($data, $compare);

		return $data;
	}

	/*
	 * Return true iff the specified event falls between the given
	 * start and end times.
	 */
	private function falls_between($event, $gmt_start = false, $gmt_end = false) {
		$falls_between = false;

		if (ICAL_EVENTS_DEBUG) {
			print "UID = [{$event['UID']}], StartTime = [{$event['StartTime']}], EndTime = [{$event['EndTime']}], Untimed = [{$event['Untimed']}], Duration = [{$event['Duration']}], gmt_start = [$gmt_start], gmt_end = [$gmt_end]\n";
		}

			$falls_between = ((! $gmt_start or $event['StartTime'] > $gmt_start)
				and (! $gmt_end or $event['EndTime'] < $gmt_end));
		if ($gmt_start and $gmt_end) {
			if ($event['StartTime'] <= $gmt_start and $event['EndTime'] >= $gmt_start) {
				$falls_between = true;
			}
		}
		return $falls_between;
	}

	/*
	 * Collapse repeating events down to nonrepeating events at the
	 * corresponding repeat time.
	 */
	private function collapse_repeats($gmt_start, $gmt_end, $limit) {
		$repeats = array();

		foreach ($this->allevents as $event) {
			if (isset($event['Repeat'])) {
				$r = $this->get_repeats_between($event, $gmt_start, $gmt_end, $limit);
				if (is_array($r) and count($r) > 0) {
					$repeats = array_merge($repeats, $r);
				}
			}
		}

		return $repeats;
	}

	/*
	 * If the specified event repeats between the given start and
	 * end times, return one or more nonrepeating events at the
	 * corresponding times.
	 * TODO: Only handles some types of repeating events
	 * TODO: Check for exceptions to the RRULE
	 */
	private function get_repeats_between($event, $gmt_start, $gmt_end, $limit) {
		$rrule = $event['Repeat'];

		$repeats = array();
		if (isset($this->ICAL_EVENTS_REPEAT_INTERVALS[$rrule['Interval']])) {
			$interval    = $this->ICAL_EVENTS_REPEAT_INTERVALS[$rrule['Interval']] * ($rrule['Frequency'] ? $rrule['Frequency'] : 1);
			$repeat_days = $this->get_repeat_days($rrule['RepeatDays']);

			$repeat = null;
			$count = 0;
			while ($count <= ICAL_EVENTS_MAX_REPEATS) {
				if ($repeat_days) {
					foreach ($repeat_days as $repeat_day) {
						$repeat = $this->get_repeat($event, $interval, $count, $repeat_day);
						if (! $this->is_duplicate($repeat, $event)
							and $this->falls_between($repeat, $gmt_start, $gmt_end)) {
							$repeats[] = $repeat;
						}

						if ($this->after_rrule_end_time($repeat, $rrule)) break;
					}
				}
				else {
					$repeat = $this->get_simple_repeat($event, $interval, $count);
					if (! $this->is_duplicate($repeat, $event)
						and $this->falls_between($repeat, $gmt_start, $gmt_end)) {
						$repeats[] = $repeat;
					}
				}

				if ($this->after_rrule_end_time($repeat, $rrule)) break;

				// Don't repeat past the user-defined limit, if one exists
				if ($limit and $count >= $limit) break;

				++$count;
			}
		}
		else {
			echo "Unknown repeat interval: ${rr['Interval']}";
		}

		return $repeats;
	}

	/*
	 * Given a string like 'nynynyn' from import_ical.php, return
	 * an array containing the weekday numbers (0 = Sun, 6 = Sat).
	 */
	private function get_repeat_days($yes_no) {
		$repeat_days = array();
		for ($i = 0; $i < strlen($yes_no); $i++) {
			if ($yes_no[$i] == 'y') $repeat_days[] = $i;
		}

		return $repeat_days;
	}

	/*
	 * Using the specified event as a base, return the repeating
	 * event the given number of intervals (in seconds) in the
	 * future on the repeat day (0 = Sun, 6 = Sat).
	 */
	private function get_repeat($event, $interval, $count, $repeat_day) {
		$repeat = $this->get_simple_repeat($event, $interval, $count);

		$date = getdate($event['StartTime']);
		$wday = $date['wday'];
		$offset = ($repeat_day - $wday) * 86400;

		$repeat['StartTime'] += $offset;
		if (isset($repeat['EndTime'])) {
			$repeat['EndTime'] += $offset;
		}

		return $repeat;
	}

	/*
	 * Using the specified event as a base, return the repeating
	 * event the given number of intervals (in seconds) in the
	 * future.
	 */
	private function get_simple_repeat($event, $interval, $count) {
		$duration = 0;

		if ($event['Duration']) {
			$duration = $event['Duration'] * 60;
		}
		else if ($event['EndTime']) {
			$duration = $event['EndTime'] - $event['StartTime'];
		}

		$repeat = $event;
		unset($repeat['Repeat']);

		$repeat['StartTime'] += $interval * $count;

		// Default to no duration
		$repeat['EndTime'] = $repeat['StartTime'];
		if ($duration > 0) {
			$repeat['EndTime'] = $repeat['StartTime'] + $duration;
		}

		// Handle timezone changes since the initial event date
		$offset = date('Z', $event['StartTime']) - date('Z', $repeat['StartTime']);
		$repeat['StartTime'] += $offset;
		$repeat['EndTime'] += $offset;

		return $repeat;
	}

	/*
	 * Return true if the specified event is passed the
	 * RRULE's end time.  If an end time isn't specified,
	 * return false.
	 */
	private function after_rrule_end_time($repeat, $rrule) {
		return ($repeat and $rrule
			and $repeat['StartTime'] and $rrule['EndTime']
			and $repeat['StartTime'] >= $rrule['EndTime']);
	}

	/*
	 * Return true if the start and end times are the same.
	 */
	private function is_duplicate($event1, $event2) {
		return ($event1['StartTime'] == $event2['StartTime']
			and $event1['EndTime'] == $event2['EndTime']);
	}

	/*
	 * Return a string representing the specified date range.
	 */
	private function format_date_range($gmt_start, $gmt_end, $untimed, $date_format, $time_format, $separator = ' - ') {
		$output = '';
		if ($this->is_today($gmt_start)){
			$output.='Today, ';
		}
		$output .= $this->format_date_range_part($gmt_start, $untimed, $this->is_today($gmt_start), $date_format, $time_format);

		if ($gmt_start != $gmt_end) {
			$output .= $separator;
			$output .= $this->format_date_range_part($gmt_end, $untimed, $this->is_same_day($gmt_start, $gmt_end), $date_format, $time_format);
		}

		$output = trim(preg_replace('/\s{2,}/', ' ', $output));

		return $output;
	}

	/*
	 * Return a string representing the specified date.
	 */
	private function format_date_range_part($gmt, $untimed, $only_use_time, $date_format, $time_format) {
		$default_format = "$date_format $time_format";

		$format = $default_format;
		if ($untimed) {
			$format = $date_format;
		}
		else if ($only_use_time) {
			$format = $time_format;
		}
		return date($format, $gmt);
	}

	/*
	 * Given a time value (as seconds since the epoch), return true
	 * iff the time falls on the current day.
	 */
	private function is_today($gmt) {
		return strftime('%j%y')==strftime('%j%y',$gmt);
	}

	/*
	 * Return true iff the two times span exactly 24 hours, from
	 * midnight one day to midnight the next.
	 */
	private function is_all_day($gmt1, $gmt2) {
		$local1 = localtime(($gmt1 <= $gmt2 ? $gmt1 : $gmt2), 1);
		$local2 = localtime(($gmt1 <= $gmt2 ? $gmt2 : $gmt1), 1);

		return (abs($gmt2 - $gmt1) == 86400
			and $local1['tm_hour'] == 0
			and $local1['tm_year'] == $local2['tm_year']);
	}

	/*
	 * Return true iff the two specified times fall on the same day.
	 */
	private function is_same_day($gmt1, $gmt2) {
		$local1 = localtime($gmt1, 1);
		$local2 = localtime($gmt2, 1);
		return ($local1['tm_mday'] == $local2['tm_mday']
			and $local1['tm_mon'] == $local2['tm_mon']
			and $local1['tm_year'] == $local2['tm_year']);
	}
	

}

?>
