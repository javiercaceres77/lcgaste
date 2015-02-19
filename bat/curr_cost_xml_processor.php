<?php
#ini_set('display_errors', 0);
$_SESSION['login'] = posix_getlogin();
pa($_SESSION['login']);
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
$xml_files_path = '/home/javipi/ccxmls/'; // '/media/usb/ccxmls/';
$bin_path = '/home/javipi/bin/'; //'/media/usb/bin/';
$num_files_to_process = 3;
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
			$accum_temp = (int) $objxml->tmpr;
			$accum_watt = (int) $objxml->ch1->watts;
			$count = 1;
			
			$arr_ins_regular = array();
			#$arr_ins_history = array();
			while(($line = fgets($file, 4096)) !== false) {
				$objxml = new SimpleXMLElement($line);
				$obj_line_time = new my_time($objxml->time);
				# check that it is the same hour as some lines could be included in incorrect files.
				if($obj_line_time->hour == $obj_file_datetime->hour)
					if($objxml->hist) {			# this is a history line
						continue;
					}
					elseif($objxml->tmpr) {		# this is a regular line (history doesn't have temperature)
						if($obj_line_time->minute == $this_min) {
							$accum_temp+= (int) $objxml->tmpr;
							$accum_watt+= (int) $objxml->ch1->watts;
							$count++;
						}
						else {
							$arr_ins_regular['CC_Time'][] = $obj_file_datetime->odate->odate .' '. $obj_line_time->hour .':'. $this_min .':00';
							$arr_ins_regular['Temperature'][] = $accum_temp / $count;
							$arr_ins_regular['Wattage'][] = $accum_watt / $count;
							$this_min = $obj_line_time->minute;
							$accum_temp+= (int) $objxml->tmpr;
							$accum_watt+= (int) $objxml->ch1->watts;
							$count = 1;
						}
					}	//	elseif($objxml->tmpr) {	
				}	//	while(($line = fgets($file, 4096)) !== false) {
				# insert the last value after the file
				
				$arr_ins_regular['CC_Time'][] = $obj_file_datetime->odate->odate .' '. $obj_line_time->hour .':'. $this_min .':00';
				$arr_ins_regular['Temperature'][] = $accum_temp / $count;
				$arr_ins_regular['Wattage'][] = $accum_watt / $count;
		}	//		if($file) {
	
		fclose($file);	
		
		pa($arr_ins_regular);
		exit();
		
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
?>
