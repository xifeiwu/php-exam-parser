<?php
//require_once ('output_html.php');
require_once ('functions-common.php');
mb_internal_encoding ( 'UTF-8' );

$global_role2index['statement'] = 0;
$global_index2role[0] = 'statement';
$row_type_statement;
$reg_statement4role = '^ *([0-9]+ *\. *\[综合题.*?\]| *\[综合题.*?\])';
$reg_statement4replace = '^ *([0-9]+ *\. *\[综合题.*?\]| *\[综合题.*?\])';
function get_statement_length($row){
    global $reg_statement4role;
    $row = preg_replace('/'.$reg_statement4role.'/', '', $row);
    return strlen($row);
}
//get the role of row [questin|option|anwser|analysis]
function get_role_by_row_subjective($row){
    global $reg_statement4role;
    global $reg_question4role;
    global $reg_option4role;
    global $reg_answer4role;
    global $reg_analysis4role;
    $role = 'unknown';
    //$reg_statement4role should be placed before $reg_question4role, as 1.[综合题]
    if (preg_match('/'.$reg_statement4role.'/', $row)){
        $role = 'statement';
    }
    else
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
    return $role;
}

//prefix of question in subjective exam may different from objective question. 
function pre_treat_rows_subjective($row){
    //question begin with 2). 2)、 replace to 2.
    $row = preg_replace('/^ *([0-9]+)\) *(?:、|\.)/', '$1.', $row);
    return $row;
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
        $row = pre_treat_rows_subjective($row);
        $row_role = get_role_by_row_subjective($row);
        if($row_role == 'statement'){
            //echo 'length:'.get_statement_length($row).'<br>';
            //if statement is blank, the following row will be added.
            if(get_statement_length($row) == 0){
                $statement_preserve = $row.get_row_by_index($arr, $i+1);
                $i++;
            }else{
                $statement_preserve = $row;
            }
            //$next_row = preg_replace('/^ *([0-9]+)\) *\./', '$1.', $arr[$i+1]);
            $next_row = pre_treat_rows_subjective($arr[$i+1]);
            $next_role = get_role_by_row_subjective($next_row);
            while('unknown' == $next_role){
                $statement_preserve = $statement_preserve.'\n'.$next_row;
                $i++;
                //$next_row = preg_replace('/^ *([0-9]+)\) *\./', '$1.', $arr[$i+1]);
                $next_row = pre_treat_rows_subjective($arr[$i+1]);
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
$type_fill_in_the_blank_subjective = 1;
$type_choice_question_subjective = 2;
$type_unknown_subjective = -1;
function array_post_treat_subjective($arr){
    global $type_multi_choice;
    global $type_single_choice;
	global $type_true_or_false_question;
	global $type_fill_in_the_blank;
    
    global $type_fill_in_the_blank_subjective;
    global $type_choice_question_subjective;
    global $type_unknown_subjective;
    
    $exam_type = $type_unknown_subjective;
    //echo '$exam_type1'.$exam_type.'<br>';
    for($i=0; $i<count($arr['questions']); $i++){
        $question = $arr['questions'][$i];        
        if(array_key_exists('type', $question)){
            $q_type = $question['type'];
            //$type_multi_choice and $type_single_choice recognized as $type_choice_question_subjective
            switch($q_type){
            	case $type_multi_choice:
            	case $type_single_choice:
            	    $q_type = $type_choice_question_subjective;
            	    break;
            	case $type_fill_in_the_blank:
            	case $type_true_or_false_question:
            	    $q_type = $type_fill_in_the_blank_subjective;
            	    break;
            }
            if(0 == $i){
                $exam_type = $q_type;
            }
            else
            {
                if($exam_type != $q_type){
                    $exam_type = $type_unknown_subjective;
                    continue;                    
                }
            }
        }else{
            $exam_type = $type_unknown_subjective;
            continue;    
        }
        echo '$exam_type:'.$exam_type.'<br>';
    }
    $arr['type'] = $exam_type; 
    return $arr;
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
            array_push($exam_array, array_post_treat_subjective($exam_cur));
            //print_r($exam_cur);
        }
    }
    return $exam_array;
}
function preview_subjective_exam($exam_array){
    global $type_fill_in_the_blank_subjective;
    global $type_choice_question_subjective;
    global $type_unknown_subjective;
    echo '<div class="demo-box">';
    echo '<div class="header">';
    echo '<h2>试题浏览</h2>';//preview_results
    //echo '<div class="view_result"><a href=\'parser-exam.php?type=array\'>查看Array格式</a></div>';
    //echo '</div>';
    for($i=0; $i<count($exam_array); $i++){
        $exam_type = '未知';
        switch($exam_array[$i]['type'])
        {
    	    case $type_fill_in_the_blank_subjective:
    	    	$exam_type = '填空类综合题';
    	    	break;
        	case $type_choice_question_subjective;
    	    	$exam_type = '选项类综合题目';
    	    	break;
    	    case $type_unknown_subjective;
    	    	$exam_type = '题型未知';
    	    	break;
        }
        //echo '<div class="statement">'.$exam_array[$i]['statement'].'</div>';
        $array_tmp = explode('\n', $exam_array[$i]['statement']);
        //print_r($array_tmp);
        $length_tmp = count($array_tmp);
        if($length_tmp > 1){
            echo '<div class=statement>';
            for($j=0; $j<$length_tmp-1; $j++){
                echo ''.$array_tmp[$j].'<br>';
            }
            echo $array_tmp[$j].'<span class=type>['.$exam_type.']</span><br>';
            echo '</div>';
        }else
        if($length_tmp == 1 && (mb_strlen($array_tmp[0]) != 0))
        {
            echo '<div class=statement>';
            echo $array_tmp[0];
            echo '<span class=type>['.$exam_type.']</span>';
            echo '</div>';
        }
        
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
        echo '题目类型:'.$exam_array[$i]['type'];
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