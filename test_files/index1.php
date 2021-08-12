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
echo "<h3><b> &emsp;".$Title."</b></h3>";

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
                    $qid=$quiz->{'id'};
                    $cid=$quiz->{'cid'};
                    $cicsql=$DB->get_record_sql('SELECT CONCAT(u.firstname, u.lastname) as cic 
                        FROM mdl_user u INNER JOIN mdl_role_assignments ra ON u.id = ra.userid 
                        INNER JOIN mdl_user_enrolments ue ON u.id = ue.userid 
                        inner join mdl_context co ON co.id=ra.contextid 
                        WHERE ra.roleid = 3 AND  co.instanceid=:cid',array("cid"=>$cid));
                    // $cicsql->{'cic'};#course incharge
                    try{
                        $results=$DB->get_records_sql('SELECT
                    --     cc.shortname AS Course_Code,
                    --     cc.fullname AS Course_Title,
                    --     (
                    --     SELECT
                    --         CONCAT(u.firstname, u.lastname)
                    --     FROM
                    --         mdl_user u
                    --     INNER JOIN mdl_role_assignments ra ON
                    --         ra.userid = u.id
                    --     INNER JOIN mdl_context co ON
                    --         co.id = ra.contextid
                    --     INNER JOIN mdl_course c ON
                    --         c.id = co.instanceid
                    --     WHERE
                    --         c.fullname = cc.fullname AND ra.roleid = 3
                    -- ) AS Course_InCharge,
                    (
                        SELECT
                            COUNT(*)
                        FROM
                            mdl_user u1
                        INNER JOIN mdl_role_assignments ra1 ON
                            ra1.userid = u1.id
                        INNER JOIN mdl_context co1 ON
                            co1.id = ra1.contextid
                        WHERE
                            co1.instanceid = cc.id AND ra1.roleid = 5
                    ) AS Number_Enrolled,
                    FROM_UNIXTIME(q.timeopen) AS Timestamp_Of_Exam_open,
                    FROM_UNIXTIME(q.timeclose) AS Timestamp_Of_Exam_close,
                    TIMESTAMPDIFF(
                        MINUTE,
                        FROM_UNIXTIME(q.timeopen),
                        FROM_UNIXTIME(q.timeclose)
                    ) AS Duration_in_minutes,
                    q.timelimit AS Time_limit,
                    q.grade AS Max_Marks,
                    (
                        SELECT
                            AVG(
                                TIMESTAMPDIFF(
                                    MINUTE,
                                    FROM_UNIXTIME(qa1.timestart),
                                    FROM_UNIXTIME(qa1.timefinish)
                                )
                            )
                        FROM
                            mdl_quiz_attempts qa1
                        WHERE
                            qa1.quiz = q.id
                    ) AS Avg_Attempt_Duration,
                    (
                        SELECT
                            MIN(
                                TIMESTAMPDIFF(
                                    MINUTE,
                                    FROM_UNIXTIME(qa1.timestart),
                                    FROM_UNIXTIME(qa1.timefinish)
                                )
                            )
                        FROM
                            mdl_quiz_attempts qa1
                        WHERE
                            qa1.quiz = q.id
                    ) AS Min_Attempt_Duration,
                    (
                        SELECT
                            MAX(
                                TIMESTAMPDIFF(
                                    MINUTE,
                                    FROM_UNIXTIME(qa1.timestart),
                                    FROM_UNIXTIME(qa1.timefinish)
                                )
                            )
                        FROM
                            mdl_quiz_attempts qa1
                        WHERE
                            qa1.quiz = q.id
                    ) AS Max_Attempt_Duration
                    FROM
                        mdl_quiz q
                    INNER JOIN mdl_course cc ON
                        cc.id = q.course
                    WHERE
                        cc.id = ? AND q.id=?',array($cid,$qid));
                        // var_dump($results);
                        echo "<br>";
                    }
                    catch (Exception $e){
                        echo $cid . " " . $qid . $e->getMessage() . "<br>";
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