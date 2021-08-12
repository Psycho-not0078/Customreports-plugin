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
$Title="Exam Modes Report";
admin_externalpage_setup('reportcustomreports','', null, '', array('pagelayout' => 'report'));
$main_sql='SELECT
                c.id as id,
                (
                    SELECT 
                    count(*)
                    FROM
                    {assign} ma
                    WHERE ma.course=c.id AND (ma.duedate>=? AND ma.duedate<=?) AND ma.name NOT LIKE CONCAT("%",c.shortname,"%Comprehensive%")  
                ) as Assignment_Count,
                (
                    SELECT 
                    count(*)
                    FROM
                    {quiz} q 
                    WHERE (q.name NOT LIKE CONCAT("%",c.shortname,"%Comprehensive%") OR q.name NOT LIKE CONCAT("%",c.shortname,"%Mid%Sem%")) and q.course=c.id AND (q.timeopen>=? AND q.timeopen<=?)
                ) as Quiz_Count,
                (
                    SELECT 
                    count(*)
                    FROM
                    {quiz} q 
                    WHERE q.name LIKE CONCAT("%",c.shortname,"%Comprehensive%") and q.course=c.id AND (q.timeopen>=? AND q.timeopen<=?)
                ) as Comprehensive_Count,
                (
                    SELECT 
                    count(*)
                    FROM
                    {quiz} q 
                    WHERE q.name LIKE CONCAT("%",c.shortname,"%Mid%Sem%") and q.course=c.id AND (q.timeopen>=? AND q.timeopen<=?)
                ) as Mid_Term_Count,
                (
                    SELECT 
                    count(*)
                    FROM
                    {quiz} q 
                    WHERE q.name LIKE CONCAT("%",c.shortname,"%Comprehensive%subjective%") and q.course=c.id AND (q.timeopen>=? AND q.timeopen<=?)
                ) as Subjective_Comprehensive,
                (
                    SELECT 
                    count(*)
                    FROM
                    {quiz} q 
                    WHERE q.name LIKE CONCAT("%",c.shortname,"%Comprehensive%objective%") and q.course=c.id AND (q.timeopen>=? AND q.timeopen<=?)
                ) as Objective_Comprehensive,
                (
                    SELECT 
                    count(*)
                    FROM
                    {assign} ma
                    WHERE ma.course=c.id AND ma.name AND (ma.duedate>=? AND ma.duedate<=?) LIKE CONCAT("%",c.shortname,"%Comprehensive%")     
                ) as Objective_Assignment
                FROM
                {course} c
                WHERE c.id=?';

$PAGE->requires->css("/report/customreports/css/bootstrap.min.css",true);
if ($_POST['export']) {
            // echo "qwerty";
            $params=array_map('intval',explode("_", $_POST['export']));
            // var_dump($params);
            $title="exam_modes_report";
            require_once __DIR__ . '/exporter.php';
            export_to_excel($title,$main_sql,$params);

}
echo $OUTPUT->header();

//---------------------------------------------------------Navbar-----------------------------------------------------------------------------
require_once __DIR__ . '/nav.php';
navbar($Title);
//--------------------------------------------------------------------------------------------------------------------------------------------
echo "<h3><b> &emsp;".$Title."</b></h3>";


$mform=new filter_form();
$mform->display();

if ($form_data = $mform->get_data()){
    $from=$form_data->{'from'};
    $to=$form_data->{'to'};
    // var_dump($form_data);
    $courses=array();
    $courses_sql=$DB->get_records_sql('SELECT 
        id,
        fullname 
    FROM {course} 
    where 
        id!=1 or 
        category!=0 
    ORDER BY 
        fullname ASC');
    foreach ($courses_sql as $key=>$value) {
        array_push($courses, $value->{'fullname'});
    }
    $prof=array();
    $prof_sql=$DB->get_records_sql('SELECT 
        u.id, 
        CONCAT(u.firstname," ", u.lastname) as name 
    FROM {user} u INNER JOIN {role_assignments} ra 
        ON ra.userid=u.id 
    WHERE 
        ra.roleid=3');
    foreach ($prof_sql as $proff) {
        array_push($prof, $proff->{'name'});
                // $courses[$id]=$course->{'fullname'};
    }
    $cid=array();
    switch ($form_data->{'options'}) {
        case 0:#on courses
            $course=$courses[$form_data->{'course'}];
            $cid_sql=$DB->get_record_sql('SELECT 
                c.id as cid 
            FROM {course} c 
            where 
                c.fullname=?',array($course));
            // var_dump($cid_sql);
            array_push($cid,$cid_sql->{'cid'});
            // var_dump($cid);
            // echo $course;
            break;
        case 1:#on cic
            $cic=$prof[$form_data->{'professor'}];

            $cid_sql=$DB->get_records_sql('SELECT
                    co.instanceid as cid
                FROM
                    {role_assignments} ra
                INNER JOIN {context} co ON
                    ra.contextid = co.id
                INNER JOIN {user} u ON
                    u.id = ra.userid
                WHERE
                    CONCAT(u.firstname," ", u.lastname) =?',array($cic));
            foreach ($cid_sql as $key => $value) {
                array_push($cid, $value->{'cid'});
            }
            // echo "qwertyyy";
            break;        
        default:
            echo "how the dell did u find me?";
            break;
    }
    // echo"qwerty";
    foreach ($cid as $id) {
        $dets_sql=$DB->get_record_sql('SELECT 
            c.shortname,
            c.fullname
        FROM
            {course} c 
        where c.id=?',array($id));
        // echo"qwerty";
        $cicsql=$DB->get_records_sql('SELECT CONCAT(u.firstname," ", u.lastname) as cic 
                FROM mdl_user u INNER JOIN mdl_role_assignments ra 
                    ON u.id = ra.userid 
                INNER JOIN mdl_user_enrolments ue 
                    ON u.id = ue.userid 
                INNER JOIN mdl_context co 
                    ON co.id=ra.contextid 
                WHERE ra.roleid = 3 AND co.instanceid=:cid',array("cid"=>$id));
        // echo"qwerty1";
        $result=$DB->get_record_sql($main_sql,array($from,$to,$from,$to,$from,$to,$from,$to,$from,$to,$from,$to,$from,$to,$id));
        // echo"qwerty2";
            // var_dump($result);
            $headder=array();
            $table = new html_table();
            $dets= new html_table();
            $dets->data[1]=array("<b>Course Code: </b> ".$dets_sql->{'shortname'},"<b>Course Name: </b> ".$dets_sql->{'fullname'});
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
            $dets->data[2]=array($fac);
            $dets->head = array();
            $keys=array_keys(get_object_vars($result));#asigning table headers
            // array_push($headder,"Sno");

            foreach ($keys as $key) {
                array_push($headder,str_replace("_"," ",ucwords($key)));
            }
            $key=array_search("id", $keys);
            unset($keys[$key]);
            $table->head = $headder;
            $row=array();
            foreach ($result as $key => $value) {
                // echo $key . " " . $value;
                if($value < 1){
                    $value="--";
                }
                if ($key!='id'){
                   array_push($row, $value);   
                }               
            }
            $table->data[]=$row;
            echo html_writer::table($dets);
            echo '"--" means no records';
            echo html_writer::table($table);  
            echo '<div class="text-center">
                                    <form action="" method="post">
                                        <button type="" id="btnExport" name="export"
                                            value="'.$id.'" class="btn btn-info">Export
                                            to Excel</button>
                                    </form>
                                </div>';    
    }

}

echo $OUTPUT->footer();