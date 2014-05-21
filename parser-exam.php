<?php
require_once('output_html.php');
require_once('functions-objective.php');
require_once('functions-subjective.php');
mb_internal_encoding('UTF-8');

do_html_header("parser-results");

session_start();
if(isset($_POST['view_test_output'])){
    //convert unrecongnized char into ordinary char.
    global $global_is_test_mode;
    $global_is_test_mode = true;
    $_SESSION['content'] = $_POST['content'];
    $content_ori = $_SESSION['content'];//$_POST['content'];
    $content_array = array();
    $pre_role_array = array();
    
    if(isset($_POST['exam-type'])){
        //echo 'exam type:'.$_POST['exam-type'].'<br>';
        switch($_POST['exam-type']){
        	case 'objective-exam':

        	    $content_array = pre_char_conv(explode("\n", $content_ori));
        	    //preview4test($content_array, null);
        	    //return;
        	    
        	    $content_array = pre_treat_rows($content_array);
        	    preview4test($content_array, null);
        	    //return;
        	    
        	    $content_array = split_rows($content_array);
        	    preview4test($content_array, null);
        	    //return;
        	    
        	    $content_array = merge_rows($content_array);
        	    preview4test($content_array, null);
        	    //return;
        	    
        	    $content_array = post_treat_rows($content_array);
        	    //preview4test($content_array, null);
        	    //return;
        	    
        	    $pre_role_array = get_and_check_roles($content_array);
        	    preview4test($content_array, $pre_role_array);
        	    //return;
        	    
        	    $exam_array = rows_to_array($content_array, $pre_role_array);
        	    
        	    preview4test($content_array, $pre_role_array);
        	    //preview_role_array(get_role_array($content_array, $pre_role_array));
        	    break;
        	case 'subjective-exam':
        	    $content_array = pre_char_conv(explode("\n", $content_ori));
        	    //preview4test($content_array, null);
        	    //return;
        	    
        	    $content_array = pre_treat_rows($content_array);
        	    preview4test($content_array, null);
        	    //return;
        	    
        	    $pre_role_array = get_roles($content_array);
        	    preview4test($content_array, $pre_role_array );
        	    //return;
        	    
        	    $exam_array = split_rows_by_statement($content_array, $pre_role_array);
        	    preview4test($content_array, null);
        	    //return;
                output_subjective_exam_array($exam_array);
        	    preview_subjective_exam($exam_array);
        	    //header('location:parser-subjective-exam.php');
                //echo_error('subjective examination is going to be build.', 'parser-exam'); 
        	    break;
        	default:
        	    echo_error('Type '.$_POST['exam-type'].' is not recognized.', 'parser-exam');
        	    break;
        }
    }else{
        echo_error('Choose examination type first.', 'parser-exam');
    }
}
if(isset($_POST['preview_results'])){
    $content_array = array();
    $pre_role_array = array();
    if(isset($_POST['exam-type'])){
        $_SESSION['content'] = $_POST['content'];
        $content_ori = $_SESSION['content'];//$_POST['content'];
        switch($_POST['exam-type']){
        	case 'objective-exam':
                //global $global_is_test_mode;
                //$global_is_test_mode = true;
                //$content_ori=$_POST['content'];
                $content_array = pre_char_conv(explode("\n", $content_ori));
                $content_array = pre_treat_rows($content_array);
                $content_array = split_rows($content_array);
                $content_array = merge_rows($content_array);
                $content_array = post_treat_rows($content_array);
                $pre_role_array = get_and_check_roles($content_array);
                $exam_array = rows_to_array($content_array, $pre_role_array);
                //$exam_array = preview_exam_stage1($exam_array);
                preview_objective_exam_stage2($exam_array);
                $_SESSION['exam_array'] = $exam_array;
                break;
            case 'subjective-exam':
                //$content_ori=$_POST['content'];
        	    $content_array = pre_char_conv(explode("\n", $content_ori));        	    
        	    $content_array = pre_treat_rows($content_array);        	    
        	    $pre_role_array = get_roles($content_array);        	    
        	    $exam_array = split_rows_by_statement($content_array, $pre_role_array);
                output_subjective_exam_array($exam_array);
        	    preview_subjective_exam($exam_array);
                $_SESSION['exam_array'] = $exam_array;
                break;
        	default:
        	    echo_error('Type '.$_POST['exam-type'].' is not recognized.', 'parser-exam');
        	    break;
        }
    }else{
        echo_error('Choose examination type first.', 'parser-exam');
    }                
}
if(isset($_REQUEST['type'])){
    $view_type = $_REQUEST['type'];
    if($view_type == "array"){
        if(isset($_SESSION['exam_array'])){
            $exam_array = $_SESSION['exam_array'];
            output_objective_exam_array($exam_array);
        }
        else{
            echo 'exam_array is not found.';
        }
    }elseif($view_type == "review"){
        if(isset($_SESSION['exam_array'])){
            $exam_array = $_SESSION['exam_array'];
            preview_objective_exam_stage2($exam_array);
        }
        else{
            echo 'exam_array is not found.';
        }
    }
}
do_html_footer();


//global $global_role2index;
//echo '<br>';
//for($t=0; $t<count($pre_role_array); $t++)
//{
//    echo $global_role2index[$pre_role_array[$t]];
//}
//echo '<br>';
?>
