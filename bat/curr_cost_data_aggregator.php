<?php
# Includes  ----------------------------------

include '../inc/config.php';
include '../inc/comm.php';
include '../inc/connect.php';
include '../inc/oops_comm.php';

date_default_timezone_set($conf_timezone);

# Sanitize get and post  ----------------------------------
sanitize_input();
unset($_POST, $_GET);

# some general use objects --------------------------------
$now = new date_time('now');
$end_time = $now->plus_mins(-60);    #usually analyse only up until one hour ago
$max_analysis_days = 3;	 #maximum number of days that is analysed

# ---------------------------------------------------------
# calculate the 1min aggregates ---------------------------
# ---------------------------------------------------------

# Select the latest 1min aggregate

$sql = 'SELECT MAX(Start_Datetime) AS Start_max
FROM Aggregate_Data
WHERE Aggregate_Period = \'min\'';

$sel_max = my_query($sql, $conex);
$start_time = new date_time(my_result($sel_max, 0, 'Start_max'));

if($start_time->datetime == '0000-00-00 00:00:00')
	$start_time = new date_time('2015-02-06 00:00:00');	// if null this is the default value

if(($end_time->timestamp - $start_time->timestamp) > ($max_analysis_days * 24 * 60 * 60))
	$end_time = $start_time->plus_mins($max_analysis_days * 24 * 60);
	
# Select raw_data *******AGGREGATED******** by 1min between $max_1min_start_datetime and (now -1 hour)
$sql = 'SELECT CAST( CC_Time AS CHAR( 16 ) ) AS minutes, 
CAST( AVG( Temperature ) AS DECIMAL( 5, 2 ) ) AS avg_tmp, 
CAST( AVG( Wattage ) AS DECIMAL( 5, 0 ) ) AS avg_watt
FROM Raw_Data
WHERE CC_Time BETWEEN \''. $start_time->datetime .'\' AND \''. $end_time->datetime .'\'
GROUP BY CAST( CC_Time AS CHAR( 16 ) ) 
ORDER BY CAST( CC_Time AS CHAR( 16 ) )';

$raw_result = my_query($sql, $conex);
$arr_ins = array();

while($record = my_fetch_array($raw_result)) {
	$obj_start_time = new date_time($record['minutes'] .':00');
	$obj_end_time = $obj_start_time->plus_mins(1);
	$arr_ins['Start_Datetime'][] 		= $obj_start_time->datetime;
	$arr_ins['End_Datetime'][] 			= $obj_end_time->datetime;
	$arr_ins['Aggregate_Period'][]		= 'min';
	$arr_ins['Average_Wattage'][]		= $record['avg_watt'];
	$arr_ins['Average_Temperature'][]	= $record['avg_tmp'];
	$arr_ins['Period_Description'][]	= $record['minutes'];
}

$ok_ins_reg = insert_array_db_multi('Aggregate_Data', $arr_ins);
$msg = 'Inserted '. count($arr_ins['Start_Datetime']) .' records of aggregate minutes';

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
