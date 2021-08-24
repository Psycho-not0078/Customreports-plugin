<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * 
 *
 * @package    report
 * @subpackage customreports
 * @copyright  2021 LSE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $DB;
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once __DIR__ . '/classes/report_customreports_filter_form.php';

admin_externalpage_setup('reportcustomreports','', null, '', array('pagelayout' => 'report'));
$main_sql='SELECT 
                q.id as q,
                q.sumgrades as Total_Marks,
                q.grade as Max_Marks,';
// echo 

$qtype_sql='Select DISTINCT qtype as type FROM {question}';
$qtypes=$DB->get_records_sql($qtype_sql);

echo gettype($qtypes);
foreach ($qtypes as $key => $value) {
    if ($key!='random'){
    $main_sql.="(
                    SELECT SUM(qe.defaultmark)
                    FROM {question} qe INNER JOIN {quiz_slots} qs on qs.questionid=qe.id
                    WHERE qs.quizid=q.id AND qe.qtype='".$key."'
                ) AS ".$key."_Type_Questions_Marks,";
    }
    else{
    $main_sql.="(
                    SELECT SUM(qe.defaultmark)
                    FROM {question} qe INNER JOIN {quiz_slots} qs on qs.questionid=qe.id
                    WHERE qs.quizid=q.id AND qe.qtype='".$key."'
                ) AS Other_Type_Questions_Marks,";
    }
}
$main_sql.='c.id as id   
            FROM 
                {course} c INNER JOIN {quiz} q 
                ON 
                    q.course=c.id
            WHERE 
                c.id=? and q.id=?';
$Title="Question Weightage Report";
// $filter = optional_param('filter', all_courses, PARAM_INT);
$PAGE->requires->css("/report/customreports/css/bootstrap.min.css",true);

echo $OUTPUT->header();
//the nav bar
//--------------------------------------------------------------------------------------------------------------------------------------------
require_once __DIR__ . '/nav.php';
navbar($Title);
//--------------------------------------------------------------------------------------------------------------------------------------------
echo "<h3><b> &emsp;".$Title."</b></h3>";
// require_once(__DIR__ . '/absentee_report.php');
// Report();

        $mform = new filter_form1();
        $mform->display();
// var_dump($main_sql);
        if ($form_data = $mform->get_data()){
                $record=array();
                $quizes=array();
                $courses=array();
                    // echo "qwerty";
                    $courses=array();
                    $courses_sql=$DB->get_records_sql('SELECT id,fullname FROM {course} where id!=1 or category!=0 ORDER BY fullname ASC');
                    foreach ($courses_sql as $key=>$value) {
                        // echo $key;
                        array_push($courses, $value->{'fullname'});
                    }
                    $course=$courses[$form_data->{'course'}];
                    // echo $course;
                    $quizes = $DB->get_records_sql('SELECT
                        q.id,
                        c.id as cid,
                        c.fullname,
                        c.shortname,
                        q.name,
                        DATE_FORMAT(
                            FROM_UNIXTIME(q.timeopen),
                                "%d-%M-%Y"
                        )AS quiz_date
                    FROM
                        mdl_quiz  q
                    INNER JOIN mdl_course c
                    ON
                        q.course = c.id 
                    WHERE
                        c.fullname =? AND 
                        q.name NOT LIKE "Assignment%"   
                    ORDER BY 
                        q.timeopen DESC',array($course));         
                $divc=0;
                if (count($quizes)!=0){
                    foreach ($quizes as $quiz){
                            echo "<br>";
                            $headder=array();
                            $table = new html_table();
                            // $quiz->{'fullname'};#course name
                            // $quiz->{'shortname'};#course code
                            // $quiz->{'name'};#quiz name
                            $cid=$quiz->{'cid'};
                            $qid=$quiz->{'id'};
                            $cicsql=$DB->get_records_sql('SELECT CONCAT(u.firstname," ", u.lastname) as cic 
                                FROM mdl_user u INNER JOIN mdl_role_assignments ra ON u.id = ra.userid 
                                INNER JOIN mdl_user_enrolments ue ON u.id = ue.userid 
                                inner join mdl_context co ON co.id=ra.contextid 
                                WHERE ra.roleid = 3 AND  co.instanceid=:cid',array("cid"=>$cid));
                            // $cicsql->{'cic'};#course incharge
                            try{
                                // echo "qwerty";
                                $results=$DB->get_records_sql($main_sql,array($cid,$qid));
                                // var_dump($results);
                                echo "<br>";
                            }
                            catch (Exception $e){
                                echo $cid . " " .$e->getMessage() . "<br>";
                            }
                            $keys=array_keys(get_object_vars($results[array_keys($results)[0]]));#asigning table headers
                            $key=array_search("id", $keys);
                            unset($keys[$key]);
                            $key=array_search("q", $keys);
                            unset($keys[$key]);
                            array_push($headder,"Sno");
                            foreach ($keys as $key) {
                                array_push($headder,str_replace("_"," ",ucwords($key)));
                            }
                            $table->head = $headder;
                            $pos=0;
                            foreach ($results as $record=>$values) {
                                $row=array();
                                // var_dump($values);
                                foreach ($values as $key=>$value) {
                                    $row[0]=$pos+1;
                                    if ($value!=NULL){
                                        if ($key!='id'){
                                            if ($key!='q') {
                                                 array_push($row, $value);   
                                             }   
                                        }    
                                    }
                                    else{
                                       array_push($row, "--"); 
                                    }
                                }

                                // var_dump($row);
                                $table->data[] = $row;
                                $pos+=1;
                            }
                
                            $dets=new html_table();
                            // $dets->data[0]=array("<b>Sno:</b> ". ($divc+1));
                            $dets->data[1]=array("<b>Course Code:</b> ".$quiz->{'shortname'},"<b>Course Name:</b> ".$quiz->{'fullname'});
                            $fac="<b>Faculty Incharge: </b>";
                            if (count($cicsql)>1){
                                $fac="<b>Faculties Incharge: </b>";
                            }
                            $pos=0;
                            foreach ($cicsql as $key => $value) {
                                $fac .= $value->{'cic'};
                                if ($pos <= count($cicsql)-2){
                                    $fac .= ", ";
                                }
                            }
                            $dets->data[2]=array($fac,"<b>Quiz Name: </b>".$quiz->{'name'});
                            $dets->head = array();
                            echo html_writer::table($dets);
                            echo html_writer::table($table);

                    }
                }
                else if (count($quizes)==0){
                    echo 'No records here';
                }

        }
echo $OUTPUT->footer();