<?php
//require_once ('output_html.php');
require_once ('functions-common.php');
mb_internal_encoding ( 'UTF-8' );

$global_role2index['statement'] = 0;
$global_index2role[0] = 'statement';
$row_type_statement;
$reg_statement4role = '^ *\[综合题.*?\].+';

//get the role of row [questin|option|anwser|analysis]
function get_role_by_row_subjective($row){
    global $reg_statement4role;
    global $reg_question4role;
    global $reg_option4role;
    global $reg_answer4role;
    global $reg_analysis4role;
    $role = 'unknown';
    if(preg_match('/'.$reg_analysis4role.'/', $row)){
        $role = 'analysis';
    }else
    if(preg_match('/'.$reg_answer4role.'/', $row)){
        $role = 'answer';
    }else
    if(preg_match('/'.$reg_option4role.'/', $row)){
        $role = 'option';
    }elseif(preg_match('/'.$reg_question4role.'/', $row)){
        $role = 'question';
    }
    elseif (preg_match('/'.$reg_statement4role.'/', $row)){
        $role = 'statement';
    }
    return $role;
}
//1.replace option prefix;2.get role by row;3.
function get_roles(&$arr){
    $arr_new = array();
    $arr_role = array();
    $row = '';
    $row_role='';
    $statement_preserve = '';
    for($i=0; $i<count($arr); $i++){
        $row = $arr[$i];
        $row = preg_replace('/^ *([0-9]+)\) *\./', '$1.', $row);
        $row_role = get_role_by_row_subjective($row);
        if($row_role == 'statement'){
            $statement_preserve = $row;
            $next_row = preg_replace('/^ *([0-9]+)\) *\./', '$1.', $arr[$i+1]);
            $next_role = get_role_by_row_subjective($next_row);
            while('unknown' == $next_role){
                $statement_preserve = $statement_preserve.$next_row;
                $i++;
                $next_row = preg_replace('/^ *([0-9]+)\) *\./', '$1.', $arr[$i+1]);
                $next_role = get_role_by_row_subjective($next_row);
            }
            array_push($arr_new, $statement_preserve);
            array_push($arr_role, 'statement');
        }else{
            array_push($arr_new, $row);
            array_push($arr_role, $row_role);
        }
    }
    //echo 'array new:<br>';
    //print_r($arr_new);
    $arr = $arr_new;
    return $arr_role;
}
function split_rows_by_statement($arr, $arr_role){
    global $global_role2index;
    $role_line='';
    for($i=0; $i<count($arr_role); $i++){
        $role_line = $role_line.$global_role2index[$arr_role[$i]];
    }
    //echo 'role line'.$role_line.'<br>';
    $exam_array = array();
    if(preg_match_all('/0[1-5]+[^0]/', $role_line, $reg, PREG_OFFSET_CAPTURE)){
        for($j=0; $j<count($reg[0]); $j++){
            $sub_role_line = $reg[0][$j][0];
            $start_pos = $reg[0][$j][1];
            $line_length = strlen($sub_role_line);
            $exam_cur = array();
            $exam_cur['statement'] = $arr[$start_pos];
            $exam_cur['questions'] = array();
            $questions = array();
            for($k=$start_pos+1; $k<($start_pos+$line_length); $k++){
                array_push($questions, $arr[$k]);
            }
            $questions = split_rows($questions);
            $questions = merge_rows($questions);
            //$questions = post_treat_rows($questions);
            $pre_role_array = get_and_check_roles($questions);
        	//preview4test($questions, $pre_role_array);
        	//return;
            $exam_cur['questions'] = rows_to_array($questions, $pre_role_array);
            array_push($exam_array, $exam_cur);
        }
    }
    return $exam_array;
}
function preview_subjective_exam($exam_array){
    echo '<div class="demo-box">';
    echo '<div class="header">';
    echo '<h2>试题浏览</h2>';//preview_results
    //echo '<div class="view_result"><a href=\'parser-exam.php?type=array\'>查看Array格式</a></div>';
    echo '</div>';
    for($i=0; $i<count($exam_array); $i++){
        echo '<div class="statement">'.$exam_array[$i]['statement'].'</div>';
        $questions = $exam_array[$i]['questions'];
        for($j=0; $j<count($questions); $j++){
            output_a_exam($questions[$j], $j);
        }
    }
    echo '<div class=footer>';
    echo '<div class=footer_button>';
    //echo '<span class=ok>';
    echo '<input class="ok" type="button" name="insert_to_database" value="没有问题，加入数据库" onclick="location.href =\'insert_to_database.php\'"></input>';
    //echo '</span><span class=back>';
    echo '<input class="back" type="button" name="insert_to_database" value="返回主页" onclick="location.href =\'index.php\'"></input>';
    //echo '</span>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

function output_subjective_exam_array($exam_array)
{
    echo '<div class="demo-box">';
    echo '<div class="header">';
    echo '<h2>数组浏览</h2>';
    echo '<div class="view_result"><a href=\'index.php\'>返回</a></div>';
    echo '</div>';
    for($i=0; $i<count($exam_array); $i++){
        echo '<br>';
        print_r($exam_array[$i]['statement']);
        echo '<br>';
        for($j=0; $j<count($exam_array[$i]['questions']); $j++){
            print_r($exam_array[$i]['questions'][$j]);
            echo '<br>';
        }
        echo '<br>';
    }
    echo '</div>';
    


}

?>