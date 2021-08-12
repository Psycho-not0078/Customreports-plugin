<?php
// echo "qwerty";
// echo '  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
//   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
//   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>';

$PAGE->requires->js(new moodle_url("https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"),true);

function navbar($Title=""){
    require_once(__DIR__ . "/config.php");
    echo '<ul class="nav nav-tabs">';
    // $url="";
    foreach ($Keys as $key) {
        $url="./".$Types[$key];
        $class="nav-link";
        $q='';
        $style='';
        if (ucwords(str_replace("_"," ",$key))==$Title){
            $q='class="active"';
            $class="nav-link active disabled";
        }
        else {
            $class="nav-link";
        }
        if ($key=="remove_Report"){
            $style='style="fontcolour:red"';
        }
        echo '<li '.$q.$style.'>';
        echo '<a class="'.$class.'"" href='.$url.$style.' >'.ucwords(str_replace("_"," ",$key))."</a>";
        echo "</li>";
        $url="";
    }

    echo '</ul>';
    echo "<br>";
} 
?>

<!-- 
<ul>
    <li><a href="./index.php">Absentee Report</a><br></li>
    <li><a href="./index1.php">Exam Time Report</a></li>
    <li>Question Usage report</li>
    <li><a href="./index2.php">Mode of exam report</a><br></li>
</ul> -->