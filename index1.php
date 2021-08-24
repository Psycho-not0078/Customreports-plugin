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

/*
 *
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
$Title="Exam Time Report";
// $filter = optional_param('filter', all_courses, PARAM_INT);
$PAGE->requires->css("/report/customreports/css/bootstrap.min.css",true);
$main_sql='SELECT
                    q.id as id,
                (
                SELECT
                    CONCAT(
                        (
                        SELECT
                            COUNT(*) AS c
                        FROM
                            {user} u1
                        INNER JOIN {role_assignments} ra1 ON
                            ra1.userid = u1.id
                        INNER JOIN {context} co1 ON
                            co1.id = ra1.contextid
                        INNER JOIN {quiz_attempts} qa1 ON
                            qa1.userid = u1.id
                        WHERE
                            co1.instanceid = cc.id AND ra1.roleid = 5 AND qa1.quiz=q.id
                    ),
                    "/",
                    (
                    SELECT
                        COUNT(*) AS c
                    FROM
                        {user} u1
                    INNER JOIN {role_assignments} ra1 ON
                        ra1.userid = u1.id
                    INNER JOIN {context} co1 ON
                        co1.id = ra1.contextid
                    WHERE
                        co1.instanceid = cc.id AND ra1.roleid = 5
                )
                    )
            ) AS Attempt_Counts,
                    FROM_UNIXTIME(q.timeopen) AS Timestamp_Of_Exam_open,
                    FROM_UNIXTIME(q.timeclose) AS Timestamp_Of_Exam_close,
                    -- TIMESTAMPDIFF(
                    --     MINUTE,
                    --     FROM_UNIXTIME(q.timeopen),
                    --     FROM_UNIXTIME(q.timeclose)
                    -- ) AS Duration_in_minutes,
                    ROUND(q.timelimit/60,2) AS Time_limit_In_Minutes,
                    (
                        SELECT
                            COUNT(*)
                        FROM
                            mdl_quiz_slots qs
                        WHERE
                            qs.quizid = q.id
                    ) AS Max_Questions,
                    q.grade AS Max_Marks,
                    (
                        SELECT
                            ROUND(
                                MIN(
                                    ABS(
                                        TIMESTAMPDIFF(
                                            MINUTE,
                                            FROM_UNIXTIME(qa1.timefinish),
                                            FROM_UNIXTIME(qa1.timestart)
                                        )
                                    )
                                ),2
                            )
                        FROM
                            {quiz_attempts} qa1
                        WHERE
                            qa1.quiz = q.id
                    ) AS Min_Attempt_Duration_In_Minutes,
                    (
                        SELECT
                            ROUND(
                                AVG(
                                    ABS(
                                        TIMESTAMPDIFF(
                                            MINUTE,
                                            FROM_UNIXTIME(qa1.timefinish),
                                            FROM_UNIXTIME(qa1.timestart)
                                        )
                                    )
                                ),2
                            )
                        FROM
                            {quiz_attempts} qa1
                        WHERE
                            qa1.quiz = q.id
                    ) AS Avg_Attempt_Duration_In_Minutes,
                    (
                        SELECT
                            ROUND(
                                MAX(
                                    ABS(
                                        TIMESTAMPDIFF(
                                            MINUTE,
                                            FROM_UNIXTIME(qa1.timefinish),
                                            FROM_UNIXTIME(qa1.timestart)
                                        )
                                    )
                                ),2
                            )
                        FROM
                            {quiz_attempts} qa1
                        WHERE
                            qa1.quiz = q.id
                    ) AS Max_Attempt_Duration_In_Minutes
                    FROM
                        {quiz} q
                    INNER JOIN {course} cc ON
                        cc.id = q.course
                    WHERE
                        cc.id = ? AND q.id=?';
if ($_POST['export']) {
            // echo "qwerty";
            $params=array_map('intval',explode("_", $_POST['export']));
            // var_dump($params);
            $title="exam_time_report";
            require_once __DIR__ . '/exporter.php';
            export_to_excel($title,$main_sql,$params);

}
echo $OUTPUT->header();
//--------------------------------------------------------------------------------------------------------------------------------------------
require_once __DIR__ . '/nav.php';
navbar($Title);
//--------------------------------------------------------------------------------------------------------------------------------------------
echo "<h3><b> &emsp;".$Title."</b></h3>";


        $mform = new report_filter_form();
        $mform->display();
        if ($form_data = $mform->get_data()){
                // var_dump($form_data);
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
                    FROM {course} c INNER JOIN {quiz} q ON 
                        q.course=c.id 
                    WHERE 
                        (
                            q.timeopen>=:open_limit AND 
                            q.timeopen<=:close_limit
                        ) AND
                        q.name NOT LIKE "Assignment%" 
                    ORDER BY 
                        q.id DESC',array("open_limit"=>$starter_limit,"close_limit"=>$ender_limit));
                }
                elseif ($form_data->{'options'}==0){
                    // echo "qwerty";
                    $courses=array();
                    $courses_sql=$DB->get_records_sql('SELECT 
                            id,
                            fullname 
                        FROM {course} 
                        where 
                            id!=1 or 
                            category!=0 
                        ORDER BY 
                            fullname');
                    foreach ($courses_sql as $key=>$value) {
                        // echo $key;
                        array_push($courses, $value->{'fullname'});
                    }
                    $course=$courses[$form_data->{'course'}];
                    // var_dump($courses[$form_data->{'course'}]);
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
                        {quiz}  q
                    INNER JOIN {course} c
                    ON
                        q.course = c.id AND
                        q.name NOT LIKE "Assignment%" 
                    WHERE
                        c.fullname =? ORDER BY q.id DESC',array($course));         
                }
                elseif ($form_data->{'options'}>1) {
                    // echo "qwert";
                    $courses=array();
                    $courses_sql=$DB->get_records_sql('SELECT 
                        id,
                        fullname 
                    FROM {course} 
                    where 
                        id!=1 and 
                        category!=0 
                    ORDER BY 
                        fullname');
                    foreach ($courses_sql as $course) {
                        array_push($courses, $course->{'fullname'});
                    }
                    // print_r($courses);
                    // echo $form_data->{'course'};
                    $course=$courses[$form_data->{'course'}];
                    // echo $course;
                    $starter_limit=$form_data->{'from'};
                    $ender_limit=$form_data->{'to'};
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
                    FROM {course} c INNER JOIN {quiz} q
                        ON q.course=c.id 
                    WHERE 
                        c.fullname=:fullname AND 
                        (q.timeopen>=:open_limit AND 
                        q.timeopen<=:close_limit) AND 
                        q.name NOT LIKE "Assignment%" 
                    ORDER BY 
                        q.id DESC',array("open_limit"=>$starter_limit,"close_limit"=>$ender_limit,"fullname"=>$course));            
                }
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
                    // echo "qwerty";
                    $cicsql=$DB->get_records_sql('SELECT 
                            CONCAT(u.firstname," ", u.lastname) as cic 
                        FROM {user} u INNER JOIN {role_assignments} ra 
                            ON u.id = ra.userid 
                        INNER JOIN {user_enrolments} ue 
                            ON u.id = ue.userid 
                        INNER JOIN {context} co 
                            ON co.id=ra.contextid 
                        WHERE ra.roleid = 3 AND  
                            co.instanceid=:cid',array("cid"=>$cid));
                    // $cicsql->{'cic'};#course incharge
                    try{
                        $results=$DB->get_records_sql($main_sql,array($cid,$qid));
                        // var_dump($results);
                        echo "<br>";
                    }
                    catch (Exception $e){
                        echo $cid . " " . $qid . $e->getMessage() . "<br>";
                    }
                    $keys=array_keys(get_object_vars($results[array_keys($results)[0]]));#asigning table headers
                    $key=array_search("id", $keys);
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
                            if ($key!='id'){
                                array_push($row, $value);   
                            }   
                            if ($value==null){
                                array_push($row, "0"); 
                            }
                        }
                        // var_dump($row);
                        $table->data[] = $row;
                        $pos+=1;
                    }
        
                    $dets=new html_table();
                    // $dets->data[0]=array("<b>Sno:</b> ". ($divc+1));
                            $dets->data[1]=array("<b>Course Code: </b> ".$quiz->{'shortname'},"<b>Course Name:</b> ".$quiz->{'fullname'});
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
                            $dets->data[2]=array($fac,"<b>Quiz Name: </b>".$quiz->{'name'},);
                    $dets->head = array();
                    echo html_writer::table($dets);
                    echo html_writer::table($table);
                    echo '<div class="text-center">
                                    <form action="" method="post">
                                        <button type="" id="btnExport" name="export"
                                            value="'.$cid.'_'.$qid.'" class="btn btn-info">Export
                                            to Excel</button>
                                    </form>
                                </div>';
            }
        }
        else if (count($quizes)==0){
            echo 'No records here';
        }

}
echo $OUTPUT->footer();