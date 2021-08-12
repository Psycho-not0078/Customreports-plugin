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

$PAGE->requires->css("/report/customreports/css/bootstrap.min.css",true);
echo $OUTPUT->header();
$Title="Exam Modes Report";
//---------------------------------------------------------Navbar-----------------------------------------------------------------------------
require_once __DIR__ . '/nav.php';
navbar($Title);
//--------------------------------------------------------------------------------------------------------------------------------------------
echo "<h3><b> &emsp;".$Title."</b></h3>";


$mform=new filter_form();
$mform->display();

if ($form_data = $mform->get_data()){
    // var_dump($form_data);
    $courses=array();
    $courses_sql=$DB->get_records_sql('SELECT fullname FROM {course}');
    foreach ($courses_sql as $key=>$value) {
        array_push($courses, $key);
    }
    $prof=array();
    $prof_sql=$DB->get_records_sql('SELECT CONCAT(u.firstname,u.lastname) as name from {user} u INNER JOIN {role_assignments} ra on ra.userid=u.id WHERE ra.roleid<=3');
    foreach ($prof_sql as $proff) {
        array_push($prof, $proff->{'name'});
                // $courses[$id]=$course->{'fullname'};
    }
    $cid="";
    switch ($form_data->{'options'}) {
        case 0:#on courses
            $course=$courses[$form_data->{'course'}];
            $cid_sql=$DB->get_record_sql('SELECT c.id as cid FROM {course} c where c.fullname=?',array($course));
            $cid=$cid_sql->{'cid'};
            // echo $course;
            break;
        case 1:#on cic
            // echo "Qwerty1";
            $cic=$prof[$form_data->{'professor'}];
            $cid_sql=$DB->get_record_sql('SELECT
                    co.instanceid as cid
                FROM
                    mdl_role_assignments ra
                INNER JOIN mdl_context co ON
                    ra.contextid = co.id
                INNER JOIN mdl_user u ON
                    u.id = ra.userid
                WHERE
                    CONCAT(u.firstname, u.lastname) =?',array($cic));
            $cid=$cid_sql->{'cid'};
            // echo "qwertyyy";
            break;        
        default:
            echo "how the dell did u find me?";
            break;
    }
    $dets_sql=$DB->get_record_sql('SELECT 
        c.shortname,
        c.fullname,
        (
            SELECT
            CONCAT(
                u1.firstname,u1.lastname
            )
            FROM
            mdl_user u1 
            INNER JOIN mdl_role_assignments ra1
            on u1.id=ra1.userid 
            INNER JOIN mdl_context co1
            on ra1.contextid=co1.id
            WHERE co1.instanceid=c.id AND ra1.roleid=3
        ) as cic
        FROM
        {course} c 
        where c.id=?',array($cid));
    $result=$DB->get_record_sql('SELECT
        -- c.shortname as Course_Code,
        -- c.fullname as Course_Name,
        -- (
        --     SELECT
        --     CONCAT(
        --         u1.firstname,u1.lastname
        --     )
        --     FROM
        --     mdl_user u1 
        --     INNER JOIN mdl_role_assignments ra1
        --     on u1.id=ra1.userid 
        --     INNER JOIN mdl_context co1
        --     on ra1.contextid=co1.id
        --     WHERE co1.instanceid=c.id AND ra1.roleid=3
        -- ) as Course_Incharge,
        (
            SELECT 
            count(*)
            FROM
            {assign} ma
            WHERE ma.course=c.id AND ma.name NOT LIKE CONCAT("%",c.shortname,"%Comprehensive%")  
        ) as Assignment_Count,
        (
            SELECT 
            count(*)
            FROM
            {quiz} q 
            WHERE q.name NOT LIKE CONCAT("%",c.shortname,"%Comprehensive%") and q.course=c.id
        ) as Quiz_Count,
        (
            SELECT 
            count(*)
            FROM
            {quiz} q 
            WHERE q.name LIKE CONCAT("%",c.shortname,"%Comprehensive%subjective%") and q.course=c.id
        ) as Subjective_Comprehensive,
        (
            SELECT 
            count(*)
            FROM
            {quiz} q 
            WHERE q.name LIKE CONCAT("%",c.shortname,"%Comprehensive%objective") and q.course=c.id
        ) as Objective_Comprehensive,
        (
            SELECT 
            count(*)
            FROM
            {assign} ma
            WHERE ma.course=c.id AND ma.name LIKE CONCAT("%",c.shortname,"%Comprehensive%")     
        ) as Objective_Assignment
        FROM
        {course} c
        WHERE c.id=?',array($cid_sql->{'cid'}));
        // var_dump($result);
        $headder=array();
        $table = new html_table();
        $dets= new html_table();
        $dets->data[1]=array("<b>Course Code:</b> ".$dets_sql->{'shortname'},"<b>Course Name:</b> ".$dets_sql->{'fullname'});
        $dets->data[2]=array("<b>Course Incharge: </b>".$dets_sql->{'cic'});
        $dets->head = array();
        $keys=array_keys(get_object_vars($result));#asigning table headers
        // array_push($headder,"Sno");
        foreach ($keys as $key) {
            array_push($headder,str_replace("_"," ",ucwords($key)));
        }
        $table->head = $headder;
        $row=array();
        foreach ($result as $key => $value) {
            // echo $key . " " . $value;
            if($value < 1){
                $value="--";
            }
            array_push($row, $value);
        }
        $table->data[]=$row;
        echo html_writer::table($dets);
        echo '"--" means no records';
        echo html_writer::table($table);
}

echo $OUTPUT->footer();