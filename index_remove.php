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
$Title="Remove Report";
require_once __DIR__ . '/nav.php';
navbar($Title);
echo "<h3><b> &emsp;".$Title."</b></h3>";
$mform=new filter_deleteform();
$mform->display();
if ($form_data=$mform->get_submitted_data()){
	// var_dump($form_data);
        $Types1=array();
        $Types['absentee_report']="index.php";
        $Types['exam_time_report']="index1.php";
        $Types['exam_modes_report']="index2.php";
        $Types['question_usage_report']="index3.php";
        try{
            $records=$DB->get_records_sql('SELECT report_title, file_name from {custom_reports}');
            foreach ($records as $record) {
                $Types[$record->{'report_title'}]=$record->{'file_name'};
            }
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
        $Keys1=array();
        foreach ($Types as $key=>$value) {
            array_push($Keys1, $key);    
        }
    $choosen_report_file=$Types[$Keys[$form_data->{'file_name'}]];
    $choosen_report=$Keys[$form_data->{'file_name'}];
    // var_dump($choosen_report,$choosen_report_file);	
    $q=$DB->record_exists_sql('SELECT * FROM {custom_reports} WHERE report_title=?',array($choosen_report));
    if ($q){
    	$DB->delete_records_select("custom_reports","report_title=?",array($choosen_report));
    }
    elseif (in_array($choosen_report, $Keys1)) {
    	$DELETE = '$Type['.$choosen_report.']='.$choosen_report_file;
		$data = file("./config.php");

		$out = array();

		foreach($data as $line) {
		    if(trim($line) != $DELETE) {
		        $out[] = $line;
		    }
		}

		$fp = fopen("./config.php", "w+");
		flock($fp, LOCK_EX);
		foreach($out as $line) {
		    fwrite($fp, $line);
		}
		flock($fp, LOCK_UN);
		fclose($fp);  
    }
}