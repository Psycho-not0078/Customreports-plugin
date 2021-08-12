<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;	
function export_to_excel($Title,$Query,$params){
	global $DB;
	$spreadsheet = new Spreadsheet();
	$worksheet = $spreadsheet->getActiveSheet();
	$dets_sql="";
	$cic_sql="";
	$course=$params[0];
	if(count($params)<2){
		$dets_sql='SELECT 
						c.shortname,
						c.fullname 
					from {course} c 
					where c.id=?';
		$cic_sql='SELECT CONCAT(u.firstname," ", u.lastname) as cic 
                  FROM {user} u INNER JOIN {role_assignments} ra 
                  		ON u.id = ra.userid 
                  INNER JOIN {user_enrolments} ue 
                  		ON u.id = ue.userid 
                  inner join {context} co 
                  		ON co.id=ra.contextid 
                  WHERE ra.roleid = 3 AND  co.instanceid=?';
	}
	if(count($params)==2){
		$dets_sql='SELECT 
						c.shortname,
						c.fullname,
						q.name, 
						DATE_FORMAT(
                            FROM_UNIXTIME(q.timeopen),
                                "%Y-%m-%d"
                        ) AS quiz_date
                         from {course} c inner join {quiz} q 
                         on q.course=c.id 
                         where c.id=? and q.id=?';
		$cic_sql='SELECT CONCAT(u.firstname," ", u.lastname) as cic 
		                  FROM {user} u INNER JOIN {role_assignments} ra 
		                  		ON u.id = ra.userid 
		                  INNER JOIN {user_enrolments} ue 
		                  		ON u.id = ue.userid 
		                  inner join {context} co 
		                  		ON co.id=ra.contextid 
		                  WHERE ra.roleid = 3 AND  co.instanceid=?';
	}
	if(count($params)>2){
		$dets_sql='SELECT 
						c.shortname,
						c.fullname,
                         from {course}
                         on q.course=c.id 
                         where c.id=?';
		$cic_sql='SELECT CONCAT(u.firstname," ", u.lastname) as cic 
		                  FROM {user} u INNER JOIN {role_assignments} ra 
		                  		ON u.id = ra.userid 
		                  INNER JOIN {user_enrolments} ue 
		                  		ON u.id = ue.userid 
		                  inner join {context} co 
		                  		ON co.id=ra.contextid 
		                  WHERE ra.roleid = 3 AND  co.instanceid=?';
	}
	$get_dets=$DB->get_record_sql($dets_sql,$params);
	$get_cic=$DB->get_records_sql($cic_sql,array($course));
	// var_dump($get_cic);
	$worksheet->setCellValue('A1',"Course Code: ");
	$worksheet->setCellValue('B1',$get_dets->{'shortname'});
	$worksheet->setCellValue('D1',"Course Name: ");
	$worksheet->setCellValue('E1',$get_dets->{'fullname'});
	$fac="Faculty Incharge: ";
	if (count($get_cic)>1){
		$fac="Faculties Incharge: ";
	}
	$worksheet->setCellValue('A2',$fac);
	$faculty="";
    $pos=0;
    foreach ($get_cic as $key => $value) {
        $faculty .= $value->{'cic'};
            if ($pos <= count($get_cic)-2){
                $faculty .= ", ";
            }
    }
    $worksheet->setCellValue('B2',$faculty);
    if(count($params)==2){
    	$worksheet->setCellValue('D2',"Quiz Name: ");
    	$worksheet->setCellValue('A3',"Quiz Date: ");
    	$worksheet->setCellValue('E2',$get_dets->{'name'});
    	$worksheet->setCellValue('B3',$get_dets->{'quiz_date'});
    }
    $results=$DB->get_records_sql($Query,$params);
   	$spos=6;
   	$alpha=range('A', 'Z');
   	$headder=array_keys(get_object_vars($results[array_keys($results)[0]]));
   	for ($i = 0; $i < count($headder); $i++) {
   		$worksheet->setCellValue($alpha[$i].'5',$headder[$i]);
   	}
   	foreach ($results as $key => $value) {
   		$i=1;
   		foreach ($value as $key => $val) {
   			$worksheet->setCellValue($alpha[$i].$spos,$val);	
   			$i+=1;
   		}
   		$spos+=1;
   	}
   	ob_clean();
	$writer = new Xlsx($spreadsheet);
	$File_name="";
	$File_name=$Title."_".$get_dets->{'shortname'};
	if (count($params)==2){
		$File_name .= "_";
		$File_name .= $get_dets->{'name'};
	}
	$File_name .= ".xlsx";
    header('Content-Type: application/vnd.openxmlformats- 
    officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$File_name.'"');
    header('Cache-Control: max-age=0');
    ob_end_clean();
	$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
	$writer->save('php://output');
	die;
}
