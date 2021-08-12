<?php  
#to be used
// $Types=array("absentee_list", "question_useage_ratio", "exam_time_report");
global $DB;
$Types=array();
$Types['absentee_report']="index.php";
$Types['exam_time_report']="index1.php";
$Types['exam_modes_report']="index2.php";
$Types['question_usage_report']="index3.php";
$Types['exam_progress_report']="index4.php";
$Types['question_weightage_report']="index5.php";
try{
	$records=$DB->get_records_sql('SELECT report_title, file_name from {custom_reports}');
	foreach ($records as $record) {
		$Types[$record->{'report_title'}]=$record->{'file_name '};
	}
	$Types['add_new_report']="index_add.php";
	$Types['remove_report']="index_remove.php";
}
catch(Exception $e){
	$Types['add_new_report']="index_add.php";	
	$Types['remove_report']="index_remove.php";
	echo $e->getMessage();
}
$Keys=array();
foreach ($Types as $key=>$value) {
	array_push($Keys, $key);	
}
