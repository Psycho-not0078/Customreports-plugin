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
$Title="Exam Progress Report";

$main_sql='SELECT
    u.id as id,
    u.idnumber AS EnrollmentNo,
    CONCAT(u.firstname, " ", u.lastname) AS NAME,
    u.email AS Email
    FROM
        {user} u
    INNER JOIN {role_assignments} ra ON
        ra.userid = u.id
    INNER JOIN {context} co ON
        ra.contextid=co.id
    INNER JOIN {course} c ON
        co.instanceid=c.id
    INNER JOIN {quiz_attempts} qa ON
        qa.userid = u.id
    INNER JOIN {quiz} q ON
        qa.quiz = q.id
    WHERE
        qa.state = ? AND c.id = ? AND q.id = ? and ra.roleid != 3 ';
$PAGE->requires->css("/report/customreports/css/bootstrap.min.css",true);
if ($_POST['export']) {
            $params=array_map('intval',explode("_", $_POST['export']));
            $title="exam_Progress_report";
            require_once __DIR__ . '/exporter.php';
            
            export_to_excel($title,$main_sql,$params);

}
echo $OUTPUT->header();
//the nav bar
//--------------------------------------------------------------------------------------------------------------------------------------------
require_once __DIR__ . '/nav.php';
navbar($Title);
//--------------------------------------------------------------------------------------------------------------------------------------------
echo "<h3><b> &emsp;".$Title."</b></h3>";
        $mform = new filter_form2();
        $mform->display();
        if ($form_data = $mform->get_data()){
                $record=array();
                $quizes=array();
                $courses=array();
                $status="inprogress";
                if ($form_data->{'type'}==1) {
                    $status="finished";
                }
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
                        q.course = c.id
                    WHERE
                        c.fullname =? AND
                        q.name NOT LIKE "Assignment%" 
                    ORDER BY 
                        q.id DESC',array($course));         
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
                            // var_dump($quiz);
                            echo "<br>";
                            $headder=array();
                            $table = new html_table();
                            // $quiz->{'fullname'};#course name
                            // $quiz->{'shortname'};#course code
                            // $quiz->{'name'};#quiz name
                            $qid=$quiz->{'id'};
                            $cid=$quiz->{'cid'};
                            $cicsql=$DB->get_records_sql('SELECT CONCAT(u.firstname," ", u.lastname) as cic 
                                FROM {user} u INNER JOIN {role_assignments} ra ON u.id = ra.userid 
                                INNER JOIN {user_enrolments} ue ON u.id = ue.userid 
                                inner join {context} co ON co.id=ra.contextid 
                                WHERE ra.roleid = 3 AND  co.instanceid=:cid',array("cid"=>$cid));
                            try{
                                // var_dump($status." ".$cid." ".$qid);
                                $results=$DB->get_records_sql($main_sql,array($status,$cid,$qid));
                                // var_dump($results);
                                echo "<br>";
                            }
                            catch (Exception $e){
                                echo $cid . " " . $qid . $e->getMessage() . "<br>";
                            }
                            if (count($results)>0){
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
                                    foreach ($values as $key=>$value) {
                                        $row[0]=$pos+1;
                                        if ($key!='id'){
                                            array_push($row, $value);   
                                        }   
                                    }
                                    $table->data[] = $row;
                                    $pos+=1;
                                }
                    
                                $dets=new html_table();
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
                                $dets->data[3]=array("<b>Quiz Date: </b>".$quiz->{'quiz_date'});
                                $dets->head = array();
                                echo html_writer::table($dets);
                                echo html_writer::table($table);
                                echo '<div class="text-center">
                                        <form action="" method="post">
                                            <button type="" id="btnExport" name="export"
                                                value="'.$status.'_'.$cid.'_'.$qid.'" class="btn btn-info">Export
                                                to Excel</button>
                                        </form>
                                    </div>';
                        }
                        // else{
                        //         $dets=new html_table();
                        //         $dets->data[1]=array("<b>Course Code:</b> ".$quiz->{'shortname'},"<b>Course Name:</b> ".$quiz->{'fullname'});
                        //         $fac="<b>Faculty Incharge: </b>";
                        //         if (count($cicsql)>1){
                        //             $fac="<b>Faculties Incharge: </b>";
                        //         }
                        //         $pos=0;
                        //         foreach ($cicsql as $key => $value) {
                        //             $fac .= $value->{'cic'};
                        //             if ($pos <= count($cicsql)-2){
                        //                 $fac .= ", ";
                        //             }
                        //         }
                        //         $dets->data[2]=array($fac,"<b>Quiz Name: </b>".$quiz->{'name'});
                        //         $dets->data[3]=array("<b>Quiz Date: </b>".$quiz->{'quiz_date'});
                        //         $dets->head = array();
                        //         echo html_writer::table($dets);
                        //     echo 'No records here';
                        // }
                    }
                }
                else if (count($quizes)==0){
                    echo 'No records here';
                }

        }
echo $OUTPUT->footer();