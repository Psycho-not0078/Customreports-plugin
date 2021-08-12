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
// echo 
$Title="Question Useage Report";
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

        $mform = new report_filter_form();
        $mform->display();
        if ($form_data = $mform->get_data()){
                // var_dump($form_data);
                // echo (count($form_data)) . "<br>";
                $record=array();
                $quizes=array();
                $courses=array();
                // echo (date("Y-m-d H:i:s",$form_data->{'date_of_exam'}));
                // echo (date("Y-m-d H:i:s",$form_data->{'date_of_exam'}+86400));
                // echo $form_data->{'options'};
                if ($form_data->{'options'}==1){
                    $starter_limit=$form_data->{'from'};
                    $ender_limit=$form_data->{'to'};
                    $quizes = $DB->get_records_sql('SELECT q.id,c.id as cid,c.fullname,c.shortname,q.name
                    FROM {course} c INNER JOIN {quiz} q ON q.course=c.id 
                    WHERE q.timeopen>=:open_limit AND q.timeopen<=:close_limit',array("open_limit"=>$starter_limit,"close_limit"=>$ender_limit));
                }
                elseif ($form_data->{'options'}==0){
                    // echo "qwerty";
                    $courses=array();
                    $courses_sql=$DB->get_records_sql('SELECT fullname FROM {course}');
                    foreach ($courses_sql as $key=>$value) {
                        // echo $key;
                        array_push($courses, $key);
                    }
                    $course=$courses[$form_data->{'course'}];
                    // echo $course;
                    $quizes = $DB->get_records_sql('SELECT
                        q.id,
                        c.id as cid,
                        c.fullname,
                        c.shortname,
                        q.name
                    FROM
                        mdl_quiz  q
                    INNER JOIN mdl_course c
                    ON
                        q.course = c.id
                    WHERE
                        c.fullname =?',array($course));         
                }
                elseif ($form_data->{'options'}>1) {
                    // echo "qwert";
                    $courses=array();
                    $courses_sql=$DB->get_records_sql('SELECT * FROM {course}');
                    foreach ($courses_sql as $course) {
                        array_push($courses, $course->{'fullname'});
                    }
                    // print_r($courses);
                    // echo $form_data->{'course'};
                    $course=$courses[$form_data->{'course'}];
                    // echo $course;
                    $starter_limit=$form_data->{'from'};
                    $ender_limit=$form_data->{'to'};
                    $quizes = $DB->get_records_sql('SELECT q.id,c.id as cid,c.fullname,c.shortname,q.name 
                    FROM {course} c INNER JOIN {quiz} q ON q.course=c.id 
                    WHERE q.timeopen>=:open_limit AND q.timeopen<=:close_limit AND c.fullname=:fullname',array("open_limit"=>$starter_limit,"close_limit"=>$ender_limit,"fullname"=>$course));            
                }
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
                            $cicsql=$DB->get_record_sql('SELECT CONCAT(u.firstname, u.lastname) as cic 
                                FROM mdl_user u INNER JOIN mdl_role_assignments ra ON u.id = ra.userid 
                                INNER JOIN mdl_user_enrolments ue ON u.id = ue.userid 
                                inner join mdl_context co ON co.id=ra.contextid 
                                WHERE ra.roleid = 3 AND  co.instanceid=:cid',array("cid"=>$cid));
                            // $cicsql->{'cic'};#course incharge
                            try{
                                // echo "qwerty";
                                $results=$DB->get_records_sql('SELECT 
                                    q.sumgrades as Total_Marks,
                                    q.grade as Max_Marks,
                                    (
                                        SELECT
                                            COUNT(*)
                                        FROM
                                             {user_enrolments} ue1
                                        INNER JOIN  {role_assignments} ra1 ON
                                            ue1.userid = ra1.userid
                                        INNER JOIN  {context} co1 ON
                                            ra1.contextid = co1.id
                                        WHERE
                                            ra1.roleid = 5 AND co1.instanceid = c.id
                                    ) as Enrollment_Count,
                                    (
                                        SELECT
                                            COUNT(*)
                                        FROM
                                             {question} q
                                        INNER JOIN  {question_categories} qc ON
                                            q.category = qc.id
                                        INNER JOIN  {context} co ON
                                            qc.contextid = co.id
                                        WHERE
                                            co.instanceid = c.id
                                    ) as Question_Bank_Count,
                                    (
                                        SELECT
                                            COUNT(*)
                                        FROM
                                             {quiz_slots} qs
                                        WHERE
                                            qs.quizid = q.id
                                    ) as Question_Used_Count,
                                    ROUND(
                                        CAST(
                                            (
                                            SELECT
                                                COUNT(*)
                                            FROM
                                                mdl_quiz_slots qs
                                            WHERE
                                                qs.quizid = q.id
                                        ) AS FLOAT
                                        ) / CAST(
                                            (
                                            SELECT
                                                COUNT(*)
                                            FROM
                                                mdl_question q
                                            INNER JOIN mdl_question_categories qc ON
                                                q.category = qc.id
                                            INNER JOIN mdl_context co ON
                                                qc.contextid = co.id
                                            WHERE
                                                co.instanceid = c.id
                                        ) AS FLOAT
                                        ),
                                        3
                                    ) AS Usage_ratio
                                        FROM 
                                        {course} c INNER JOIN {quiz} q ON q.course=c.id
                                        wHERE c.id=? and q.id=? ',array($cid,$qid));
                                // var_dump($results);
                                echo "<br>";
                            }
                            catch (Exception $e){
                                echo $cid . " " .$e->getMessage() . "<br>";
                            }
                            $keys=array_keys(get_object_vars($results[array_keys($results)[0]]));#asigning table headers
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
                                    array_push($row, $value);
                                }
                                // var_dump($row);
                                $table->data[] = $row;
                                $pos+=1;
                            }
                
                            $dets=new html_table();
                            // $dets->data[0]=array("<b>Sno:</b> ". ($divc+1));
                            $dets->data[1]=array("<b>Course Code:</b> ".$quiz->{'shortname'},"<b>Course Name:</b> ".$quiz->{'fullname'});
                            $dets->data[2]=array("<b>Course Incharge: </b>".$cicsql->{'cic'},"<b>Quiz Name:</b>".$quiz->{'name'});
                            $dets->head = array();
                            echo html_writer::table($dets);
                            echo html_writer::table($table);
                            $divc+=1;
                    }
                }
                else if (count($quizes)==0){
                    // echo $courses[$form_data->{'course'}];
                    echo 'No records here';
                }

        }
echo $OUTPUT->footer();