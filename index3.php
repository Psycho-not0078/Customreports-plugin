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
                qu.id as id,
                qu.sumgrades AS Total_Marks,
                qu.grade AS Max_Marks,
                (
                        SELECT
                            COUNT(*)
                        FROM
                            {user} u1
                        INNER JOIN {role_assignments} ra1 ON
                            ra1.userid = u1.id
                        INNER JOIN {context} co1 ON
                            co1.id = ra1.contextid
                        WHERE
                            co1.instanceid = c.id AND ra1.roleid = 5
                ) AS Number_Enrolled,
            (
                SELECT
                    COUNT(*)
                FROM
                    mdl_question qq
                INNER JOIN mdl_question_categories qc ON
                    qq.category = qc.id
                INNER JOIN mdl_context co ON
                    qc.contextid = co.id
                WHERE
                    co.instanceid = c.id
            ) AS Question_Bank_Count,
            (
                SELECT
                    COUNT(*)
                FROM
                    mdl_quiz_slots qs
                WHERE
                    qs.quizid = qu.id
            ) AS Question_Used_Count,
            (
                SELECT
                    X.c / Y.c
                FROM
                    (
                    SELECT
                        COUNT(*) as c,
                        qq.course AS cid,
                        qq.id as id
                    FROM
                        mdl_quiz_slots qs
                    INNER JOIN mdl_quiz qq ON
                        qs.quizid = qq.id
                    WHERE
                        qq.id = ?
                ) X
            JOIN(
                SELECT COUNT(*) as c,
                    co.instanceid AS cid
                FROM
                    mdl_question qq
                INNER JOIN mdl_question_categories qc ON
                    qq.category = qc.id
                INNER JOIN mdl_context co ON
                    qc.contextid = co.id
                WHERE
                    co.instanceid = ?
            ) Y
            ON
                X.cid = Y.cid
                
            ) as Usage_Ratio
            FROM
                mdl_course c
            INNER JOIN mdl_quiz qu ON
                qu.course = c.id
            WHERE
                c.id = ? AND qu.id = ?';
// echo 
$Title="Question Usage Report";
// $filter = optional_param('filter', all_courses, PARAM_INT);
$PAGE->requires->css("/report/customreports/css/bootstrap.min.css",true);
// if ($_POST['export']) {
//             // echo "qwerty";
//             $params=array_map('intval',explode("_", $_POST['export']));
//             // var_dump($params);
//             $title="question_useage_report";
//             require_once __DIR__ . '/exporter.php';
//             export_to_excel($title,$main_sql,$params);

// }
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
                    ORDER BY q.timeopen DESC',array($course));         
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
                                $results=$DB->get_records_sql($main_sql,array($qid,$cid,$cid,$qid));
                                // var_dump($results);
                                echo "<br>";
                            }
                            catch (Exception $e){
                                echo $cid . " " .$e->getMessage() . "<br>";
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
                            $dets->data[3]=array("<b>Quiz Date: </b>".$quiz->{'quiz_date'});
                            $dets->head = array();
                            echo html_writer::table($dets);
                            echo html_writer::table($table);
                            // echo '<div class="text-center">
                            //         <form action="" method="post">
                            //             <button type="" id="btnExport" name="export"
                            //                 value="'.$cid.'_'.$qid.'" class="btn btn-info">Export
                            //                 to Excel</button>
                            //         </form>
                            //     </div>';
                    }
                }
                else if (count($quizes)==0){
                    echo 'No records here';
                }

        }
echo $OUTPUT->footer();