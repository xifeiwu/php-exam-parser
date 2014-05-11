<?php
require_once('output_html.php');
mb_internal_encoding('UTF-8');

do_html_header("parser-results");

if(isset($_REQUEST['type'])){
    $view_type = $_REQUEST['type'];
    if($view_type == "array"){
        if(isset($_SESSION['exam_array'])){
            $exam_array = $_SESSION['exam_array'];
            preview_exam_array($exam_array);
        }
        else{
            echo 'exam_array is not found.';
        }
    }elseif($view_type == "review"){
        if(isset($_SESSION['exam_array'])){
            $exam_array = $_SESSION['exam_array'];
            preview_exam_stage1($exam_array);
        }
        else{
            echo 'exam_array is not found.';
        }
    }
}

do_html_footer();
?>