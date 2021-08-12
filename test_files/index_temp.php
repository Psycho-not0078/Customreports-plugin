<?php
global $DB;
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once __DIR__ . '/../classes/report_struct.php';
// require_once(__DIR__ . "/config.php");

admin_externalpage_setup('reportcustomreports','', null, '', array('pagelayout' => 'report'));
$PAGE->requires->css("/report/customreports/css/bootstrap.min.css",true);
echo $OUTPUT->header();
// require_once __DIR__ . "/nav.php";
// $arr=navbar1();
// $Types=$arr[0];
// $Keys=$arr[1];
// echo '<div class="tab-content">';
// foreach ($Types as $key => $value) {
// 	echo '<div id=#'.$key.' class="tab-pane">';
// 	echo dirname(__DIR__) ."/". $value;
// 	// try{
// 		// require_once(dirname(__FILE__) ."/". $value);
// 		// report();
// 	// }
// 	// catch(Exception $e){
// 	// 	echo $e->getMessage();
// 	// }
// 	// echo "<br>";
// 	// if ($key="absentee_report"){
// 	// 	// echo $value;
// 	// 	require_once("./". $value);
// 	// 	report();
// 	// }
// 	echo '</div>';
// }
// // echo '</div>';
$class=new Report_struct(1,"qwerty");
// $class->main();
echo $OUTPUT->footer();