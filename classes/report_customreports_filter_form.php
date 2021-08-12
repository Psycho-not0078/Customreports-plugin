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
 * Report main page
 *
 * @package    report
 * @subpackage customreports
 * @copyright  2021 LSE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once $CFG->libdir . '/formslib.php';



class report_filter_form extends moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form; // Don't forget the underscore! 
        $courses=array();
        $courses_sql=$DB->get_records_sql('SELECT CONCAT(fullname,": ",shortname) as fullname,id FROM {course} where id!=1 or category!=0 ORDER BY fullname ASC');
        foreach ($courses_sql as $course) {
            array_push($courses, $course->{'fullname'});
            // $courses[$id]=$course->{'fullname'};
        }   
        $choices=array("By Course","By Date Range","Both");
        $mform->addElement( 'select', 'options', 'Select', $choices);

        $placeholder = array('placeholder' => 'Select any','noselectionstring' => "No Selections");
        $mform->addElement('autocomplete', 'course', "Select Course", $courses, $placeholder);
        // $mform->setDefault('');
        $mform->addElement('date_selector', 'from', 'From');
        $mform->addElement('date_selector', 'to', 'To');   
        $mform->disabledIf('course','options','eq',1);
        $mform->disabledIf('from','options','eq',0);
        $mform->disabledIf('to','options','eq',0);

        $this->add_action_buttons(false, get_string('btn_submit', 'report_customreports'));
    }
}

class filter_form extends moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form; // Don't forget the underscore! 
        $courses=array();
        $courses_sql=$DB->get_records_sql('SELECT CONCAT(fullname,": ",shortname) as fullname,id FROM {course} where id!=1 or category!=0 ORDER BY fullname ASC');
        // array_push($courses, "All courses");
        foreach ($courses_sql as $course) {
            array_push($courses, $course->{'fullname'});
            // $courses[$id]=$course->{'fullname'};
        }
        $prof=array();
        $prof_sql=$DB->get_records_sql('SELECT CONCAT(u.firstname," ", u.lastname) as name from {user} u INNER JOIN {role_assignments} ra on ra.userid=u.id WHERE ra.roleid=3');
        foreach ($prof_sql as $proff) {
            array_push($prof, $proff->{'name'});
            // $courses[$id]=$course->{'fullname'};
        }
        $choices=array("By Course","By Professor");
        $mform->addElement( 'select', 'options', 'Select', $choices);
        $placeholder = array('placeholder' => 'Select any','noselectionstring' => "No Selections");
        $mform->addElement('autocomplete', 'course', "Select Course", $courses, $placeholder);
        $mform->addElement('autocomplete', 'professor', "Select Professor", $prof, $placeholder);
        $mform->addElement('date_selector', 'from', 'From');
        $mform->addElement('date_selector', 'to', 'To');
        $mform->disabledIf('course','options','eq',1);
        $mform->disabledIf('Professor','options','eq',0);
        $this->add_action_buttons(false, get_string('btn_submit', 'report_customreports'));
    }
}
class filter_form1 extends moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form; // Don't forget the underscore! 
        $courses=array();
        $courses_sql=$DB->get_records_sql('SELECT CONCAT(fullname,": ",shortname) as fullname,id FROM {course} where id!=1 or category!=0 ORDER BY fullname ASC');
        // array_push($courses, "All courses");
        foreach ($courses_sql as $course) {
            array_push($courses, $course->{'fullname'});
            // $courses[$id]=$course->{'fullname'};
        }
        $placeholder = array('placeholder' => 'Select any','noselectionstring' => "No Selections");
        $mform->addElement('autocomplete', 'course', "Select Course", $courses, $placeholder);
        $this->add_action_buttons(false, get_string('btn_submit', 'report_customreports'));
    }
}
class filter_form2 extends moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form; // Don't forget the underscore! 
        $courses=array();
        $courses_sql=$DB->get_records_sql('SELECT CONCAT(fullname,": ",shortname) as fullname,id FROM {course} where id!=1 or category!=0 ORDER BY fullname ASC');
        foreach ($courses_sql as $course) {
            array_push($courses, $course->{'fullname'});
            // $courses[$id]=$course->{'fullname'};
        }   
        $choices=array("By Course","By Date Range","Both");
        $mform->addElement( 'select', 'options', 'Select', $choices);

        $placeholder = array('placeholder' => 'Select any','noselectionstring' => "No Selections");
        $mform->addElement('autocomplete', 'course', "Select Course", $courses, $placeholder);
        // $mform->setDefault('');
        $mform->addElement('date_selector', 'from', 'From');
        $mform->addElement('date_selector', 'to', 'To');   
        $choices1=array("Inprogress","Finished");
        $mform->addElement( 'select', 'type', 'Select Status', $choices1);
        $mform->disabledIf('course','options','eq',1);
        $mform->disabledIf('from','options','eq',0);
        $mform->disabledIf('to','options','eq',0);

        $this->add_action_buttons(false, get_string('btn_submit', 'report_customreports'));
    }
}
class filter_addform extends moodleform {
    public function definition() {
        global $DB;
        $mform=$this->_form;
        $mform->addElement('text', 'report_title', "Enter The Report Title");
        $mform->addElement('filepicker', 'report_file', "Enter Report File", null,
                   array('accepted_types' => 'php'));
        $this->add_action_buttons(false, get_string('btn_submit', 'report_customreports'));

    }
}
class filter_deleteform extends moodleform {
    public function definition() {
        global $DB;
        $mform=$this->_form;
        $Types=array();
        $Types['absentee_report']="index.php";
        $Types['question_useage_report']="index3.php";
        $Types['exam_time_report']="index1.php";
        $Types['exam_modes_report']="index2.php";
        try{
            $records=$DB->get_records_sql('SELECT report_title, file_name from {custom_reports}');
            foreach ($records as $record) {
                $Types[$record->{'report_title'}]=$record->{'file_name '};
            }
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
        $Keys=array();
        foreach ($Types as $key=>$value) {
            array_push($Keys, $key);    
        }
        $placeholder = array('placeholder' => 'Select any','noselectionstring' => "No Selections");
        $mform->addElement('select', 'file_name', "Select Report", $Keys, $placeholder);
        $this->add_action_buttons(false, get_string('btn_submit', 'report_customreports'));

    }
}
class multi_filter extends moodleform {
    private $filter_type;
    public function __construct($type='', $action=null, $customdata=null, $method='post', $target='', $attributes=null, $editable=true,$ajaxformdata=null) {
        global $CFG, $FULLME;
        // no standard mform in moodle should allow autocomplete with the exception of user signup
        if (empty($attributes)) {
            $attributes = array('autocomplete'=>'off');
        } else if (is_array($attributes)) {
            $attributes['autocomplete'] = 'off';
        } else {
            if (strpos($attributes, 'autocomplete') === false) {
                $attributes .= ' autocomplete="off" ';
            }
        }


        if (empty($action)){
            // do not rely on PAGE->url here because dev often do not setup $actualurl properly in admin_externalpage_setup()
            $action = strip_querystring($FULLME);
            if (!empty($CFG->sslproxy)) {
                // return only https links when using SSL proxy
                $action = preg_replace('/^http:/', 'https:', $action, 1);
            }
            //TODO: use following instead of FULLME - see MDL-33015
            //$action = strip_querystring(qualified_me());
        }
        // Assign custom data first, so that get_form_identifier can use it.
        $this->_customdata = $customdata;
        $this->_formname = $this->get_form_identifier();
        $this->_ajaxformdata = $ajaxformdata;

        $this->_form = new MoodleQuickForm($this->_formname, $method, $action, $target, $attributes, $ajaxformdata);
        if (!$editable){
            $this->_form->hardFreeze();
        }
        $this->filter_type=$type;
        $this->definition();

        $this->_form->addElement('hidden', 'sesskey', null); // automatic sesskey protection
        $this->_form->setType('sesskey', PARAM_RAW);
        $this->_form->setDefault('sesskey', sesskey());
        $this->_form->addElement('hidden', '_qf__'.$this->_formname, null);   // form submission marker
        $this->_form->setType('_qf__'.$this->_formname, PARAM_RAW);
        $this->_form->setDefault('_qf__'.$this->_formname, 1);
        $this->_form->_setDefaultRuleMessages();

        // Hook to inject logic after the definition was provided.
        $this->after_definition();

        // we have to know all input types before processing submission ;-)
        $this->_process_submission($method);
    }#dont touch this b'cause it is held by duck-tape as of now ie i just added one line, and i am gonna stop now.......
    public function definition() {
        // var_dump($this->filter_type);
        global $DB;
        $mform = $this->_form; // Don't forget the underscore! 

        switch ($this->filter_type) {
            case 0:#course
                $courses=array();
                $courses_sql=$DB->get_records_sql('SELECT id,CONCAT(fullname,": ",shortname) as fullname FROM {course} where id!=1 or category!=0 ORDER BY fullname ASC');
                foreach ($courses_sql as $course) {
                    array_push($courses, $course->{'fullname'});
                }
                $placeholder = array('placeholder' => 'Select any','noselectionstring' => "No Selections");
                $mform->addElement('autocomplete', 'course', "Select Course", $courses, $placeholder);
                break;
            case 1:#date range
                $mform->addElement('date_selector', 'from', 'From');
                $mform->addElement('date_selector', 'to', 'To');   
                break;
            case 2:#professor 
                $prof=array();
                $prof_sql=$DB->get_records_sql('SELECT CONCAT(u.firstname,u.lastname) as name from {user} u INNER JOIN {role_assignments} ra on ra.userid=u.id WHERE ra.roleid<=3 ');
                foreach ($prof_sql as $proff) {
                    array_push($prof, $proff->{'name'});
                    // $courses[$id]=$course->{'fullname'};
                }
                $mform->addElement('autocomplete', 'professor', "Select Professor", $prof, $placeholder);
                break;
            case 3:#course and professor
                $courses=array();
                $courses_sql=$DB->get_records_sql('SELECT CONCAT(fullname,": ",shortname) as fullname,id FROM {course} where id!=1 or category!=0 ORDER BY fullname ASC');
                // array_push($courses, "All courses");
                foreach ($courses_sql as $course) {
                    array_push($courses, $course->{'fullname'});
                    // $courses[$id]=$course->{'fullname'};
                }
                $prof=array();
                $prof_sql=$DB->get_records_sql('SELECT CONCAT(u.firstname,u.lastname) as name from {user} u INNER JOIN {role_assignments} ra on ra.userid=u.id WHERE ra.roleid<=3 ');
                foreach ($prof_sql as $proff) {
                    array_push($prof, $proff->{'name'});
                    // $courses[$id]=$course->{'fullname'};
                }
                $choices=array("By Course","By Professor");
                $mform->addElement( 'select', 'options', 'Select', $choices);
                $placeholder = array('placeholder' => 'Select any','noselectionstring' => "No Selections");
                $mform->addElement('autocomplete', 'course', "Select Course", $courses, $placeholder);
                $mform->addElement('autocomplete', 'professor', "Select Professor", $prof, $placeholder);
                $mform->disabledIf('course','options','eq',1);
                $mform->disabledIf('Professor','options','eq',0);
                break;
            case 4:#course and daterange
                $courses=array();
                $courses_sql=$DB->get_records_sql('SELECT CONCAT(fullname,": ",shortname) as fullname,id FROM {course} where id!=1 or category!=0 ORDER BY fullname ASC');
                foreach ($courses_sql as $course) {
                    array_push($courses, $course->{'fullname'});
                    // $courses[$id]=$course->{'fullname'};
                }   
                $choices=array("By Course","By Date Range","Both");
                $mform->addElement( 'select', 'options', 'Select', $choices);

                $placeholder = array('placeholder' => 'Select any','noselectionstring' => "No Selections");
                $mform->addElement('autocomplete', 'course', "Select Course", $courses, $placeholder);
                // $mform->setDefault('');
                $mform->addElement('date_selector', 'from', 'From');
                $mform->addElement('date_selector', 'to', 'To');   
                $mform->disabledIf('course','options','eq',1);
                $mform->disabledIf('from','options','eq',0);
                $mform->disabledIf('to','options','eq',0);
                break;            
            default:
                $courses=array();
                $courses_sql=$DB->get_records_sql('SELECT CONCAT(fullname,": ",shortname) as fullname,id FROM {course} where id!=1 or category!=0 ORDER BY fullname ASC');
                foreach ($courses_sql as $course) {
                    array_push($courses, $course->{'fullname'});
                    // $courses[$id]=$course->{'fullname'};
                }   
                $prof=array();
                $prof_sql=$DB->get_records_sql('SELECT CONCAT(u.firstname,u.lastname) as name from {user} u INNER JOIN {role_assignments} ra on ra.userid=u.id WHERE ra.roleid<=3 ');
                foreach ($prof_sql as $proff) {
                    array_push($prof, $proff->{'name'});
                    // $courses[$id]=$course->{'fullname'};
                }
                $choices=array("By Course","By Date Range","By Professor","All Of The Above");
                $mform->addElement( 'select', 'options', 'Select', $choices);

                $placeholder = array('placeholder' => 'Select any','noselectionstring' => "No Selections");
                $mform->addElement('autocomplete', 'course', "Select Course", $courses, $placeholder);
                $mform->addElement('autocomplete', 'professor', "Select Professor", $prof, $placeholder);
                // $mform->setDefault('');
                $mform->addElement('date_selector', 'from', 'From');
                $mform->addElement('date_selector', 'to', 'To');   
                $mform->disabledIf('course','options','eq',1);
                $mform->disabledIf('from','options','eq',0);
                $mform->disabledIf('to','options','eq',0);
                break;
        }

        $this->add_action_buttons(false, get_string('btn_submit', 'report_customreports'));
    }
  
}
?>