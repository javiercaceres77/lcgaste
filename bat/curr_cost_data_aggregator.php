<?php
# Includes  ----------------------------------

include '/var/www/lcgaste/inc/config_dom.php';
include $conf_include_path . 'comm.php';
include $conf_include_path . 'connect.php';
include $conf_include_path . 'oops_comm.php';

date_default_timezone_set($conf_timezone);

# Sanitize get and post  ----------------------------------
sanitize_input();
unset($_POST, $_GET);

# some general use objects and variables ------------------
$num_hours_to_aggregate = 12;
//$now = new date_time('now');
//$end_time = $now->plus_mins(-60);	#usually analyse only up until one hour ago
//$max_analysis_days = 3;	 #maximum number of days that is analysed

# SELECT 1 hour from the raw_data and calculate the 10min and 1h aggregates.
# Just be careful that the raw_data table has at least 1 hour of data
# repeat 12 times

# Select the latest 1min aggregate
$sql = 'SELECT MAX( CC_Time ) as Start_max FROM Raw_Data';
$sel_max_1m = my_query($sql, $conex);
$obj_max_1m = new date_time(my_result($sel_max_1m, 0, 'Start_max'));

while($num_hours_to_aggregate > 0) {
	$num_hours_to_aggregate--;
	
	# SELECT the max from the aggregate table
	$sql = 'SELECT MAX(End_Datetime) AS Start_max FROM Aggregate_Data WHERE Aggregate_Period_Type = \'10min\'';
	$sel_max_10m = my_query($sql, $conex);
	$obj_max_10m = new date_time(my_result($sel_max_10m, 0, 'Start_max'));

	if($obj_max_10m->datetime == '0000-00-00 00:00:00')
		$obj_max_10m = new date_time('2015-02-06 00:00:00');
	
	//$obj_max_plus60 = $obj_max_10m->plus_mins(60);	# 1 more minute taken as the while needs to loop one more time for the insert to be triggered
	$obj_max_plus59 = $obj_max_10m->plus_mins(59);
	# if there aren't more data on raw_data, exit the loop;
	if($obj_max_1m->timestamp < $obj_max_plus59->timestap)
		break;

	# Select next hour from raw data
	$sql = 'SELECT * FROM Raw_Data WHERE CC_Time BETWEEN \''. $obj_max_10m->datetime .'\' AND \''. $obj_max_plus59->datetime .'\' ORDER BY CC_Time ASC';
	$sel_raw = my_query($sql, $conex);
	
	$arr_ins_10m = array();
	$this_10m = substr($obj_max_10m->minute,0,1);
	$max_watt = -100000;
	$min_watt = 100000;
	$max_temp = -100;
	$min_temp = 100;
	$count = 0;
	$sum_watt = 0;
	$sum_temp = 0;
	
	while($record = my_fetch_array($sel_raw)) {
		$this_time = $record['CC_Time'];
		$rec_10m = substr($this_time,14,1);
		if($rec_10m != $this_10m) {
			$arr_ins_10m['Start_Datetime'][]		= substr($this_time,0,14) . $this_10m .'0:00';
			$arr_ins_10m['End_Datetime'][]			= substr($this_time,0,14) . $this_10m .'9:00';
			$arr_ins_10m['Aggregate_Period_Type'][]	= '10min';
			$arr_ins_10m['Average_Wattage'][]		= $sum_watt / $count;
			$arr_ins_10m['Average_Temperature'][]	= $sum_temp / $count;
			$arr_ins_10m['Max_Wattage'][]			= $max_watt;
			$arr_ins_10m['Min_Wattage'][]			= $min_watt;
			$arr_ins_10m['Max_Temperature'][]		= $max_temp;
			$arr_ins_10m['Min_Temperature'][]		= $min_temp;
			$arr_ins_10m['Max_Watt_Datetime'][]		= $max_watt_time;
			$arr_ins_10m['Min_Watt_Datetime'][]		= $min_watt_time;
			$arr_ins_10m['Max_Temp_Datetime'][]		= $max_temp_time;
			$arr_ins_10m['Min_Temp_Datetime'][]		= $min_temp_time;
			$arr_ins_10m['Period_Description'][]	= $this_time;
			$arr_ins_10m['Complete_Period_Ind'][]	= 'Y';
			$arr_ins_10m['Average_Watt_Weight'][]	= $count / 60;
			$arr_ins_10m['Average_Temp_Weight'][]	= $count / 60;
			# reset variables
			$this_10m = $rec_10m;
			$max_watt = -100000;
			$min_watt = 100000;
			$max_temp = -100;
			$min_temp = 100;
			$count = 0;
			$sum_watt = 0;
			$sum_temp = 0;
			
		}	//	if($rec_10m != $this_10m) {
		if($max_watt < $record['Wattage']) 		{	$max_watt = $record['Wattage'];		$max_watt_time = $record['CC_Time'];	}
		if($min_watt > $record['Wattage']) 		{	$min_watt = $record['Wattage'];		$min_watt_time = $record['CC_Time'];	}
		if($max_temp < $record['Temperature']) 	{	$max_temp = $record['Temperature'];	$max_temp_time = $record['CC_Time'];	}
		if($min_temp > $record['Temperature']) 	{	$min_temp = $record['Temperature'];	$min_temp_time = $record['CC_Time'];	}
		$count++;
		$sum_watt+= $record['Wattage'];
		$sum_temp+= $record['Temperature'];
	}	//	while($record = my_fetch_array($sel_raw)) {
	
	#INSERT again because the while loop exits before the last element is inserted
	$arr_ins_10m['Start_Datetime'][]		= substr($this_time,0,14) . $this_10m .'0:00';
	$arr_ins_10m['End_Datetime'][]			= substr($this_time,0,14) . $this_10m .'9:00';
	$arr_ins_10m['Aggregate_Period_Type'][]	= '10min';
	$arr_ins_10m['Average_Wattage'][]		= $sum_watt / $count;
	$arr_ins_10m['Average_Temperature'][]	= $sum_temp / $count;
	$arr_ins_10m['Max_Wattage'][]			= $max_watt;
	$arr_ins_10m['Min_Wattage'][]			= $min_watt;
	$arr_ins_10m['Max_Temperature'][]		= $max_temp;
	$arr_ins_10m['Min_Temperature'][]		= $min_temp;
	$arr_ins_10m['Max_Watt_Datetime'][]		= $max_watt_time;
	$arr_ins_10m['Min_Watt_Datetime'][]		= $min_watt_time;
	$arr_ins_10m['Max_Temp_Datetime'][]		= $max_temp_time;
	$arr_ins_10m['Min_Temp_Datetime'][]		= $min_temp_time;
	$arr_ins_10m['Period_Description'][]	= $this_time;
	$arr_ins_10m['Complete_Period_Ind'][]	= 'Y';
	$arr_ins_10m['Average_Watt_Weight'][]	= $count / 60;
	$arr_ins_10m['Average_Temp_Weight'][]	= $count / 60;

	# now calculate the 1 hour agg. all the others (day, week, month, year) are re-calculated every time based on the one hour
	$arr_ins_1h = array();
	$max_watt = -100000;
	$min_watt = 100000;
	$max_temp = -100;
	$min_temp = 100;
	$count = 0;
	$sum_watt = 0;
	$sum_temp = 0;

	foreach($arr_ins_10m['Start_Datetime'] as $i => $rec) {
		if($max_watt < $arr_ins_10m['Max_Wattage'][$i])		{	$max_watt = $arr_ins_10m['Max_Wattage'][$i];		$max_watt_time = $arr_ins_10m['Max_Watt_Datetime'][$i];	}
		if($min_watt > $arr_ins_10m['Min_Wattage'][$i])		{	$min_watt = $arr_ins_10m['Min_Wattage'][$i];		$min_watt_time = $arr_ins_10m['Min_Watt_Datetime'][$i];	}
		if($max_temp < $arr_ins_10m['Max_Temperature'][$i])	{	$max_temp = $arr_ins_10m['Max_Temperature'][$i];	$max_temp_time = $arr_ins_10m['Max_Temp_Datetime'][$i];	}
		if($min_temp > $arr_ins_10m['Min_Temperature'][$i])	{	$min_temp = $arr_ins_10m['Min_Temperature'][$i];	$min_temp_time = $arr_ins_10m['Min_Temp_Datetime'][$i];	}
		$count++;
		$sum_watt+= $arr_ins_10m['Average_Wattage'][$i];
		$sum_temp+= $arr_ins_10m['Average_Temperature'][$i];
	}	//	foreach($arr_ins_10m['Start_Datetime'] as $i => $rec) {
	
	
	$arr_ins_1h['Start_Datetime']			= $obj_max_10m->datetime;
	$arr_ins_1h['End_Datetime']				= $obj_max_plus59->datetime;
	$arr_ins_1h['Aggregate_Period_Type']	= 'hour';
	$arr_ins_1h['Average_Wattage']			= $sum_watt / $count;
	$arr_ins_1h['Average_Temperature']		= $sum_temp / $count;
	$arr_ins_1h['Max_Wattage']				= $max_watt;
	$arr_ins_1h['Min_Wattage']				= $min_watt;
	$arr_ins_1h['Max_Temperature']			= $max_temp;
	$arr_ins_1h['Min_Temperature']			= $min_temp;
	$arr_ins_1h['Max_Watt_Datetime']		= $max_watt_time;
	$arr_ins_1h['Min_Watt_Datetime']		= $min_watt_time;
	$arr_ins_1h['Max_Temp_Datetime']		= $max_temp_time;
	$arr_ins_1h['Min_Temp_Datetime']		= $min_temp_time;
	$arr_ins_1h['Period_Description']		= $obj_max_10m->datetime;
	$arr_ins_1h['Complete_Period_Ind']		= 'Y';
	$arr_ins_1h['Average_Watt_Weight']		= 1;
	$arr_ins_1h['Average_Temp_Weight']		= 1;
	
	pa($arr_ins_1h);
	pa($arr_ins_10m);
exit();	

	# Select the next hour from the 1min aggregate table
	$sql = 'SELECT * FROM Raw_Data 
	WHERE (CC_Time >= (
		SELECT MAX(End_Datetime) AS MAX_END_DATETIME FROM Aggregate_Data WHERE Aggregate_Period_Type = \'10min\'
	)
	ORDER BY CC_Time ASC
	LIMIT 0,59';

}	//	while($num_hours_to_aggregate > 0) {


exit();



# ---------------------------------------------------------
# calculate the 1min aggregates ---------------------------
# ---------------------------------------------------------

# Select the latest 1 hour aggregate
//$sql = 'SELECT MAX(End_Datetime) AS Start_max FROM Aggregate_Data WHERE Aggregate_Period_Type = \'10min\'';
//$sel_max_10m = my_query($sql, $conex);
//$obj_max_10m = new date_time(my_result($sel_max_10m, 0, 'Start_max'));

# Select the latest 1min aggregate
//$sql = 'SELECT MAX( CC_Time ) as Start_max FROM Raw_Data';
//$sel_max_1m = my_query($sql, $conex);
//$obj_max_1m = new date_time(my_result($sel_max_1m, 0, 'Start_max'));




# Select all 1min aggregates older than max_10
$sql = 'SELECT * FROM Raw_Data WHERE CC_Time > \''. $obj_max_10m->datetime .'\' ORDER BY CC_Time ASC';
$sel_raw = my_query($sql, $conex);

$num_loops = $num_hours_to_aggregate * 60;
while($record = my_fetch_array($sel_raw)) {
	$num_loops--;
	if($num_loops < 0) break;
	
	
}


while($num_hours_to_aggregate >= 0) {
	$num_hours_to_aggregate--;
	
}


if($ok_ins_reg)
{	
	$sql = 'DELETE FROM Raw_Data WHERE CC_Time BETWEEN \''. $start_time->datetime .'\' AND \''. $end_time->datetime .'\'';
	// delete records from raw table? 
	write_log_db('Current Cost', 'INSERT aggregate OK', $msg, 'current_cost_data_aggregator.php');
}
else {
	write_log_db('Current Cost', 'INSERT aggregate ERROR', $msg, 'curr_cost_xml_processor.php');
}


?>
