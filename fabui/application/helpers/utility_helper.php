<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
if ( !function_exists('arrayFromPost'))
{
	/**
	 * 
	 * crate list array from post data, converting keys with '-' in arrays
	 * 
	 * es: feeder-show => $item['feeder']['show']
	 * 
	 */
	function arrayFromPost($data)
	{
		$stringToEval = '$stringArray = array(); ';
		foreach($data as $key => $value){
			$temp = explode('-', $key);
			if(count($temp)> 1){
				$tempString = '$stringArray';
				foreach($temp as $k){
					$tempString .= '["'.$k.'"]';
				}
				$stringToEval .= $tempString;
			}else{
				$stringToEval .= '$stringArray["'.$key.'"]';
			}
			
			if( is_array($value) )
			{
				$partial = arrayFromPost($value);
				$stringToEval .= ' = array(';
				$first = True;
				foreach($partial as $val)
				{
					if($first)
					{
						$first = False;
					}
					else
						$stringToEval .= ', ';
					$stringToEval .= '"'.$val.'"';
				}
				$stringToEval .= ' );';
			}
			else
			{
				$stringToEval .= ' = "'.$value.'";';
			}
		}
		return eval($stringToEval.' return $stringArray;');
	}	
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('flatten_array'))
{
	/**
	 * 
	 * 
	 */
	function flatten_array($array, $glue = '.', $path = [])
	{
		$flat = array();
		
		foreach($array as $key => $value)
		{
			$new_path = $path;
			$new_path[] = $key;
			$flat_key = implode($glue, $new_path);
			
			if( is_array($value) )
			{
				$sub_array = flatten_array($value, $glue, $new_path);
				$flat = array_merge($flat, $sub_array);
			}
			else
			{
				$flat[$flat_key] = $value;
			}
		}
		
		return $flat;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('is_assoc'))
{
	/**
	 * 
	 * 
	 */
	function is_assoc($array)
	{
		// Keys of the array	
		$keys = array_keys($array);
		// If the array keys of the keys match the keys, then the array must
    	// not be associative (e.g. the keys array looked like {0:0, 1:1...}).
		return array_keys($keys) !== $keys;
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('date_to_mysql'))
{
	function date_to_mysql($date, $time = FALSE, $separator = "/") {

		$date = explode(' ', $date);

		$temp = explode($separator, $date[0]);

		$return = $temp[2] . "-" . $temp[1] . "-" . $temp[0];

		if ($time == TRUE) {
			$return .= ' ' . $date[1];
		}

		return $return;
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('dateDiff'))
{
	// Time format is UNIX timestamp or
	// PHP strtotime compatible strings
	function dateDiff($time1, $time2, $precision = 6) {
		
		_("second"); _("seconds");
		_("minute"); _("minutes");
		_("hour"); _("hours");
		_("day"); _("days");
		_("month"); _("months");
		_("year"); _("years");
		
		
		// If not numeric then convert texts to unix timestamps
		if (!is_int($time1)) {
			$time1 = strtotime($time1);
		}
		if (!is_int($time2)) {
			$time2 = strtotime($time2);
		}

		// If time1 is bigger than time2
		// Then swap time1 and time2
		if ($time1 > $time2) {
			$ttime = $time1;
			$time1 = $time2;
			$time2 = $ttime;
		}

		// Set up intervals and diffs arrays
		$intervals = array('year', 'month', 'day', 'hour', 'minute', 'second');
		$diffs = array();

		// Loop thru all intervals
		foreach ($intervals as $interval) {
			// Create temp time from time1 and interval
			$ttime = strtotime('+1 ' . $interval, $time1);
			// Set initial values
			$add = 1;
			$looped = 0;
			// Loop until temp time is smaller than time2
			while ($time2 >= $ttime) {
				// Create new temp time from time1 and interval
				$add++;
				$ttime = strtotime("+" . $add . " " . $interval, $time1);
				$looped++;
			}

			$time1 = strtotime("+" . $looped . " " . $interval, $time1);
			$diffs[$interval] = $looped;
		}

		$count = 0;
		$times = array();
		// Loop thru all diffs
		foreach ($diffs as $interval => $value) {
			// Break if we have needed precission
			if ($count >= $precision) {
				break;
			}
			// Add value and interval
			// if value is bigger than 0
			if ($value > 0) {
				// Add s if value is not 1
				if ($value != 1) {
					$interval .= "s";
				}
				// Add value and interval to times array
				//$times[] = $value . " " . $interval;
				$times[$interval] = sprintf("%02d", $value);
				$count++;
			}
		}

		// Return string with times
		return $times;
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getTimePast'))
{
	function getTimePast($created_date) {

		$differences = dateDiff(time(), $created_date);

		if (isset($differences["year"]) || isset($differences["years"])) {

			if (isset($differences["year"]))
				return $differences["year"] . " year";

			if (isset($differences["years"]))
				return $differences["years"] . " years";
		}

		if (isset($differences["month"]) || isset($differences["months"])) {

			if (isset($differences["month"]))
				return $differences["month"] . " month";

			if (isset($differences["months"]))
				return $differences["months"] . " months";
		}

		if (isset($differences["day"]) || isset($differences["days"])) {

			if (isset($differences["day"]))
				return $differences["day"] . " day";

			if (isset($differences["days"]))
				return $differences["days"] . " days";
		}

		if (isset($differences["hour"]) || isset($differences["hours"])) {

			if (isset($differences["hour"]))
				return $differences["hour"] . " hr";

			if (isset($differences["hours"]))
				return $differences["hours"] . " hrs";
		}

		if (isset($differences["minute"]) || isset($differences["minutes"])) {

			if (isset($differences["minute"]))
				return $differences["minute"] . " min";

			if (isset($differences["minutes"]))
				return $differences["minutes"] . " min";
		}

		if (isset($differences["second"]) || isset($differences["seconds"])) {

			if (isset($differences["second"]))
				return $differences["second"] . " second";

			if (isset($differences["seconds"]))
				return $differences["seconds"] . " seconds";
		}
	}
}

/**
 *
 */
 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('sumTimes'))
{
	function sumTimes($times) {
		
		$seconds = 0;
		foreach ($times as $time) {
			list($hour, $minute, $second) = explode(':', $time);
			$seconds += $hour * 3600;
			$seconds += $minute * 60;
			$seconds += $second;
		}
		$hours = floor($seconds / 3600);
		$seconds -= $hours * 3600;
		$minutes = floor($seconds / 60);
		$seconds -= $minutes * 60;
		// return "{$hours}:{$minutes}:{$seconds}";
		return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
	}
}

 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('timeToSeconds'))
{
	function timeToSeconds($time) {

		$time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);

		sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);

		return $hours * 3600 + $minutes * 60 + $seconds;

	}
}
 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('startsWith'))
{
	function startsWith($haystack, $needle) {
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
	}
}
 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('endsWith'))
{
	function endsWith($haystack, $needle) {
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}
}




?>
