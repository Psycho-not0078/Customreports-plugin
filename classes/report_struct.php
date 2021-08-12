<?php  
global $DB;
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');


class Report_struct{
    private $report_title;#the title of the report
    private $condition;
    private $conditions_array;
    private $loop_param;
    private $Query;#the main query
    private $Query_parameters;
    private $filter_type;#which type of filter to be used, i.e. only on course or only on date range or both.
    private $filter_form;
    private $form_data;
    private $details;#must be a 2D array 
    private $results;
    private $dets;
    private $table;
    public function __construct($filter_type=3,$title=""){
        $this->filter_type=$filter_type;
        $this->report_title=$title;
        $this->dets=new html_table();
        $this->dets->head = array();
        $this->table=new html_table();
        echo "<h3><b> &emsp;".$this->report_title."</b></h3>";
        require_once __DIR__ . '/nav.php';
        navbar($this->report_title);
    }
    public function main($loop_over_sql=''){
        require_once __DIR__ . '/report_customreports_filter_form.php';
        $this->filter_form=new multi_filter($this->filter_type);
        $this->filter_form->display();
        if ($this->filter_form->get_data()){
            $this->form_data = $this->filter_form->get_data();
            $this->set_condition($loop_over_sql);
        }
    }
    private function set_condition($loop_over_sql){
        switch ($this->filter_type) {
            case 0:
                $courses=array();
                $courses_sql=$DB->get_records_sql('SELECT fullname,id FROM {course}');
                foreach ($courses_sql as $course) {
                    array_push($courses, $course->{'fullname'});
                }
                $this->condition=$course[$this->form_data->{'course'}];
                break;
            case 1:
                $this->condition=array($this->form_data->{'from'},$this->form_data->{'to'});
                break;
            case 2:
                $prof=array();
                $prof_sql=$DB->get_records_sql('SELECT CONCAT(u.firstname,u.lastname) as name from {user} u INNER JOIN {role_assignments} ra on ra.userid=u.id WHERE ra.roleid<=3 ');
                foreach ($prof_sql as $proff) {
                    array_push($prof, $proff->{'name'});
                }
                $this->condition=$prof[$this->form_data->{'professor'}];
                break;
            case 3:
                switch ($this->form_data->{'option'}) {
                    case 0:
                        $courses=array();
                        $courses_sql=$DB->get_records_sql('SELECT fullname,id FROM {course}');
                        foreach ($courses_sql as $course) {
                            array_push($courses, $course->{'fullname'});
                        }
                        $this->condition=$course[$this->form_data->{'course'}];
                        break;
                    case 1:
                        $prof=array();
                        $prof_sql=$DB->get_records_sql('SELECT CONCAT(u.firstname,u.lastname) as name from {user} u INNER JOIN {role_assignments} ra on ra.userid=u.id WHERE ra.roleid<=3 ');
                        foreach ($prof_sql as $proff) {
                            array_push($prof, $proff->{'name'});
                        }
                        $this->condition=$prof[$this->form_data->{'professor'}];
                        break;
                    default:
                        echo "Internal Error";
                        break;
                }
                break;
            case 4:
                switch ($this->form_data->{'option'}) {
                    case 0:
                        $courses=array();
                        $courses_sql=$DB->get_records_sql('SELECT fullname,id FROM {course}');
                        foreach ($courses_sql as $course) {
                            array_push($courses, $course->{'fullname'});
                        }
                        $this->condition=$course[$this->form_data->{'course'}];
                        break;
                    case 1:
                        $this->condition=array($this->form_data->{'from'},$this->form_data->{'to'});
                        break;
                    case 2:
                        $this->condition=$this->condition=array($this->form_data->{'from'},$this->form_data->{'to'});
                        $courses=array();
                        $courses_sql=$DB->get_records_sql('SELECT fullname,id FROM {course}');
                        foreach ($courses_sql as $course) {
                            array_push($courses, $course->{'fullname'});
                        }
                        array_push($this->condition,$course[$this->form_data->{'course'}]);
                        break;
                    default:
                        echo "Internal Error";
                        break;
                }
                break;
            default:
                echo "Internal Error";
                break;
        }
        $this->details=$DB->get_records_sql($loop_over_sql,$this->condition);
    }
    public function set_report_title($title){
        $this->report_title=$title;
    }
    public function execute_query($query,$param){
        $this->Query=$query;
        $this->Query_parameter=$param;
        $this->results=$DB->get_records_sql($this->Query,$this->Query_parameters);
    }
    public function print_details(){
        $keys=array_keys(get_object_vars($this->details[array_keys($this->details)[0]]));
        $arr=array();
        for ($i = 0; $i < count($keys); $i++) {
            array_push($arr,$this->details[$keys[$i]]);
            if ($i%2==0 and $i!=0){
                array_push($this->dets->data, $arr);
                $arr=array();
            }
        }
        echo html_writer::table($this->dets);
    }
    // public function print_main_table(){

    // }
    
    
}
