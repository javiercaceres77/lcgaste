<?php
#ini_set('display_errors', 1);
# Includes  ----------------------------------
include '/var/www/lcgaste/inc/config_dom.php';
include $conf_include_path . 'comm.php';
include $conf_include_path . 'connect.php';
include $conf_include_path . 'oops_comm.php';
date_default_timezone_set($conf_timezone);

# Sanitize get and post  ----------------------------------
sanitize_input();
unset($_POST, $_GET);

# some general use objects --------------------------------
//$now = new date_time('now');

# some variables use objects --------------------------------
$xml_files_path = '/home/javipi/ccxmls/';
$bin_path = '/home/javipi/bin/'; //'/media/usb/bin/';
$num_files_to_process = 18;

# scan directory
$arr_directory = scandir($xml_files_path);
# remove the last file (the one that is being written now)
array_pop($arr_directory);

foreach($arr_directory as $file_name) {
	$num_files_to_process--;	if($num_files_to_process <= 0) break;
	
	if($file_name <> '.' && $file_name <> '..') {
    	$file_date = substr($file_name, 8, 4) .'-'. substr($file_name, 12, 2) .'-'. substr($file_name, 14, 2);
		$file_time = substr($file_name, 17, 2) .':'. substr($file_name, 19, 2);
		$obj_file_datetime = new date_time($file_date, $file_time);
		# open file
		if($file_date)
			$file = fopen($xml_files_path . $file_name, "r");

		if($file) {
			# Get one line and initialise values
			$line = fgets($file, 4096);
			$objxml = new SimpleXMLElement($line);
			$obj_line_time = new my_time($objxml->time);
			$this_min = $obj_line_time->minute;
			$accum_temp = (float) $objxml->tmpr;
			$accum_watt = (float) $objxml->ch1->watts;
			$count = 1;

			$arr_ins_regular = array();
			
			$arr_history_times = build_arr_times($obj_file_datetime);
			//pa($arr_history_times);
			while(($line = fgets($file, 4096)) !== false) {
				$objxml = new SimpleXMLElement($line);
				$obj_line_time = new my_time($objxml->time);
				# check that it is the same hour as some lines could be included in incorrect files.
				if($obj_line_time->hour == $obj_file_datetime->hour)
				{
					if($objxml->hist) {			# this is a history line
						foreach($objxml->hist->data[0] as $id => $value) {
							if($arr_history_times[$id])
								if($arr_history_times[$id]['t'] == 'hour')
									$arr_history_times[$id]['w'] = (float) $value / 2;
								else
									$arr_history_times[$id]['w'] = (float) $value;
						}
					}
					elseif($objxml->tmpr) {		# this is a regular line (history doesn't have temperature)
						continue;
						if($obj_line_time->minute == $this_min) {
							if($objxml->tmpr <> 0 || $objxml->ch1->watts <> 0) {
								$accum_temp+= (float) $objxml->tmpr;
								$accum_watt+= (float) $objxml->ch1->watts;
								$count++;
							}
						}
						else {
							if($objxml->tmpr <> 0 || $objxml->ch1->watts <> 0) {
								$arr_ins_regular['CC_Time'][] = $obj_file_datetime->odate->odate .' '. $obj_line_time->hour .':'. $this_min .':00';
								$arr_ins_regular['Temperature'][] = $accum_temp / $count;
								$arr_ins_regular['Wattage'][] = $accum_watt / $count;
								$this_min = $obj_line_time->minute;
								$accum_temp = (float) $objxml->tmpr;
								$accum_watt = (float) $objxml->ch1->watts;
								$count = 1;
							}
						}
					}	//	elseif($objxml->tmpr) {
				}	//	if($obj_line_time->hour == $obj_file_datetime->hour)
			}	//	while(($line = fgets($file, 4096)) !== false) {

			# insert the last value after the file
			if($count > 0) {
				$arr_ins_regular['CC_Time'][] = $obj_file_datetime->odate->odate .' '. $obj_line_time->hour .':'. $this_min .':00';
				$arr_ins_regular['Temperature'][] = $accum_temp / $count;
				$arr_ins_regular['Wattage'][] = $accum_watt / $count;
			}

			# insert the historical data into the Accumulated table
			# select the max datetimes for each data type:
			$sql = 'SELECT MAX(PERIOD_DATETIME) AS MAX_PERIOD, PERIOD_TYPE FROM Accumulated_Wattage GROUP BY PERIOD_TYPE';
			$sel_max = my_query($sql, $conex);
			$arr_max = array();
			while($record = my_fetch_array($sel_max))
				$arr_max[$record['PERIOD_TYPE']] = $record['MAX_PERIOD'];
			
			$arr_ins = array();
			foreach($arr_history_times as $arr_time_watt){
				if($arr_max[$arr_time_watt['t']] < $arr_time_watt['d']) {
					$arr_ins['Period_Type'][] = $arr_time_watt['t'];
					$arr_ins['Period_Datetime'][] = $arr_time_watt['d'];
					$arr_ins['KWh'][] = $arr_time_watt['w'];
				}
			}
			
			if(count($arr_ins)) {
				$ok_ins_hist = insert_array_db_multi('Accumulated_Wattage', $arr_ins);
				$msg = 'Inserted history '. count($arr_ins['KWh']) . ' records';
				if($ok_ins_hist)
					write_log_db('Current Cost', 'INSERT OK', $msg, 'curr_cost_xml_processor.php');
				else
					write_log_db('Current Cost', 'INSERT ERROR', $msg, 'curr_cost_xml_processor.php');
			}
		}	//		if($file) {
	
		fclose($file);
	
		$ok_ins_reg = insert_array_db_multi('Raw_Data', $arr_ins_regular);
		$msg = 'Inserted '. count($arr_ins_regular['CC_Time']) .' records from file: '. $file_name;
		if($ok_ins_reg)
		{
			//rename($xml_files_path . $file_name, $bin_path . $file_name);					# move file to $bin_path
			if(unlink($xml_files_path . $file_name))
				echo 'borrado';
			else
				echo 'error ';
			write_log_db('Current Cost', 'INSERT OK', $msg, 'curr_cost_xml_processor.php');
		}
		else {
			write_log_db('Current Cost', 'INSERT ERROR', $msg, 'curr_cost_xml_processor.php');
		}
	}	//if($file_name <> '.' && $file_name <> '..')
}	//foreach($arr_directory as $file_name) {

function build_arr_times($obj_curr_datetime) {
	$floor_date = new date_time('2015-02-06'); // do not measure any data before that date
	$min_h = 4;	$max_h = 744;
	$min_d = 1;	$max_d = 90;
	$min_m = 1;	$max_m = 84;
	$ret_array = array();
	
	for($i = $min_h; $i < $max_h; $i+=2) {
		$str_index = 'h'. add_zeroes2($i,3);
		$arr_datetime = $obj_curr_datetime->plus_mins(-$i * 60);
		if($arr_datetime->timestamp > $floor_date->timestamp) {
			$ret_array[$str_index]['d'] = $arr_datetime->datetime;
			$ret_array[$str_index]['w'] = '';
			$ret_array[$str_index]['t'] = 'hour';
		}
	}
	
	for($i = $min_d; $i < $max_d; $i++) {
		$str_index = 'd'. add_zeroes2($i,3);
		$arr_datetime = $obj_curr_datetime->plus_period('day',-$i);
		$arr_datetime = $arr_datetime->calculate_start_of_period('day');
		if($arr_datetime->timestamp > $floor_date->timestamp) {
			$ret_array[$str_index]['d'] = $arr_datetime->datetime;
			$ret_array[$str_index]['w'] = '';
			$ret_array[$str_index]['t'] = 'day';
		}
	}
	
	for($i = $min_m; $i < $max_m; $i++) {
		$str_index = 'm'. add_zeroes2($i,3);
		$arr_datetime = $obj_curr_datetime->plus_period('month',-$i);
		$arr_datetime = $arr_datetime->calculate_start_of_period('month');
		if($arr_datetime->timestamp > $floor_date->timestamp) {
			$ret_array[$str_index]['d'] = $arr_datetime->datetime;
			$ret_array[$str_index]['w'] = '';
			$ret_array[$str_index]['t'] = 'mth';
		}
	}
	
	return $ret_array;
}


?>
