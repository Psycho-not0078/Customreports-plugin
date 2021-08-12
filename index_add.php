<?php
global $DB;
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once __DIR__ . '/classes/report_customreports_filter_form.php';

admin_externalpage_setup('reportcustomreports','', null, '', array('pagelayout' => 'report'));
// echo 
$Title="Add new";
$PAGE->requires->css("/report/customreports/css/bootstrap.min.css",true);
echo $OUTPUT->header();
$Title="Add New Report";
require_once __DIR__ . '/nav.php';
navbar($Title);
echo "<h3><b> &emsp;".$Title."</b></h3>";
$mform=new filter_addform();
$mform->display();

if ($form_data=$mform->get_data()){
	// var_dump($form_data);
	$file_name=$mform->get_new_filename('report_file');
	// var_dump ($file_name);
	$report_title=$form_data->{'report_title'};
	$insert_data=array('report_title'=>$file_name,'file_name'=>$file_name);
	// print_r($insert_data);
	$ins=(object)$insert_data;
	$ins->id=$DB->insert_record("custom_reports",$ins);
	// var_dump(__DIR__."/");
	try{
		$success=$mform->save_file('report_file',__DIR__."/".$mform->get_new_filename('report_file'),false);
		var_dump($success);
		
	}
	catch (Exception $e){
		echo $e->getMessage();
	}
}

echo $OUTPUT->footer();

