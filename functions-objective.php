<?php
require_once('functions-common.php');
mb_internal_encoding('UTF-8');

$global_role2index['question'] = 1;
$global_role2index['option'] = 2;
$global_role2index['answer'] = 3;
$global_role2index['analysis'] = 4;
$global_role2index['unknown'] = 5;
//$global_role2index['blank'] = 5;
$global_index2role[1] = 'question';
$global_index2role[2] = 'option';
$global_index2role[3] = 'answer';
$global_index2role[4] = 'analysis';
$global_index2role[5] = 'unknown';
//$global_index2role[5] = 'blank';

$row_type_question;
$row_type_option_only;
$row_type_option_answer;
$row_type_option_answer_analysis;
$row_type_answer_analysis;
$reg_row_type_question_type1;
$reg_row_type_question_type2;
$reg_row_type_question;
$reg_row_type_option_only;
$reg_row_type_option_answer;
$reg_row_type_option_answer_analysis;
$reg_row_type_answer_analysis;

//space befoer can exist or not.
$reg_question4role = '^ *(?:[0-9]+ *[\.:]|第[0-9]+题 *[\.:]|第[0-9]+题 *[^\.:]|< *[0-9]+ *> *[\.:]).+';
$reg_option4role = '^(?: *[A-Fa-f][\.:]| *[0-9]\)|[A-Fa-f][^A-Fa-f\.:]).+';
$reg_answer4role = '^ *(?:[0-9]+ *[\.:]|< *[0-9]+ *> *[\.:])*\[*(?:参考答案|正确答案|标准答案|您的答案|正确答案|答案|本题正确答案为|答案及解析|答案)\]*(?::.+?|[^:].+?)';
$reg_analysis4role = '^ *\[*(?:试题解析|参考解析|答案解析|本题分析|试题点评|本题来源|本题考点|本题解析|解析)\]*(:.+|[^:].+)';

//space must be placed before question,option,answer for split.
//$reg_question4split = ' *[0-9]+.*?\.| *第[0-9]+题[:.]| *第[0-9]+题.?';
$reg_question4split = ' *(?:[0-9]+ *[\.:]|第[0-9]+题 *[\.:]|第[0-9]+题 *[^\.:]|< *[0-9]+ *> *[\.:])';
$reg_option4split = '[A-Fa-f][\.:]|[0-9]\)|  *[A-Fa-f]|^[A-Fa-f]';
$reg_answer4split = ' (?:[0-9]+ *[\.:]|< *[0-9]+ *> *[\.:])*\[*(?:参考答案|正确答案|标准答案|正确答案|答案|本题正确答案为|答案及解析|答案)\]*.*?';//[A-F√×T]
$reg_analysis4split = ' *\[*(?:试题解析|参考解析|答案解析|本题分析|试题点评|本题来源|本题考点|本题解析|解析)\]*(:.+?|[^:].+?)';

$reg_question4replace = '^ *(?:[0-9]+ *[\.:]|第[0-9]+题 *[\.:]|第[0-9]+题 *[^\.:]|< *[0-9]+ *> *[\.:])';
$reg_option4replace = '^ *[A-Fa-f][\.:]*|^ *[0-9]\)';
$reg_answer4replace = '^ *(?:[0-9]+ *[\.:]|< *[0-9]+ *> *[\.:])*\[*(?:参考答案|正确答案|标准答案|您的答案|正确答案|答案|本题正确答案为|答案及解析|答案)\]*:*';
$reg_analysis4replace = '^ *\[*(?:试题解析|参考解析|答案解析|本题分析|试题点评|本题来源|本题考点|本题解析|解析)\]*:*';

$type_fill_in_the_blank = 0;
$type_multi_choice = 1;
$type_single_choice = 2;
$type_true_or_false_question = 3;
$answer_in_question_multi_choice = 0;
$answer_in_question_single_choice = 1;
$separate_answer_from_question = 2;
$multi_choice = 3;
$single_choice = 4;
$option_less_than_two = 5;
$true_or_false_question = 6;
$fill_in_the_blank = 7;
$type_array = array(
'answer_in_question_multi_choice', 'answer_in_question_single_choice',
'separate_answer_analysis_from_question', 'separate_answer_from_question',
'multi_choice', 'single_choice', 'true_or_false_question', 'fill_in_the_blank',
);
//get the role of row [questin|option|anwser|analysis]
function get_role_by_row($row){
    global $reg_question4role;
    global $reg_option4role;
    global $reg_answer4role;
    global $reg_analysis4role;
    $role = "unknown";
    if(preg_match('/'.$reg_analysis4role.'/', $row)){
        $role = "analysis";
    }else
    //answer must before question. [0-9]+\.正确答案
    if(preg_match('/'.$reg_answer4role.'/', $row)){
        $role = "answer";
    }else
    //option role must before question role. ^ [0-9]
    if(preg_match('/'.$reg_option4role.'/', $row)){
        $role = "option";
    }elseif(preg_match('/'.$reg_question4role.'/', $row)){
        //echo 'row: '.$row.'<br>';
        //echo 'reg: '.$reg_question4role.'<br>';
        $role = "question";
    }
    /*
    elseif ($row == ''){
        $role = "blank";
    }
    */
    //echo $row.': '.$role.'<br>';
    return $role;
}

//convert unregular char to specific char to easy regular expression
function pre_char_conv($arr){
    $search = array(
        "　",        "：",        "．",                       "（",//"、",
        "）",        "ａ",        "ｂ",        "ｃ",        "ｄ",        "ｅ",
        "ｆ",        "Ａ",        "Ｂ",        "Ｃ",        "Ｄ",        "Ｅ",
        "Ｆ",        "\t",        '【',        '】',        "\r",        '０',
        '１',        '２',        '３',        '４',        '５',        '６',
        '７',        '８',        '９',
    );
    $replace = array(
        "",        ":",        ".",                        "(",//".",
        ")",        "A",        "B",        "C",        "D",        "E",
        "F",        "A",        "B",        "C",        "D",        "E",
        "F",        "",        "[",        "]",        "",        '0',
        '1',        '2',        '3',        '4',        '5',        '6',
        '7',        '8',        '9',
    );
    $arr_new = array();
    $j = 0;
    for($i=0; $i < count($arr); $i++){	
        $row = $arr[$i];
        if(mb_strlen($row) == 0){
            continue;
        }
        $row = str_replace($search, $replace, $row);
/*
        //if row begin with [A-Fa-f] or [0-9]+, dot will be followed.
        if(preg_match('/^ *[A-Fa-f]/', $row)){
	    if(!preg_match('/^ *[A-Fa-f]\./', $row)){
                $row = preg_replace('/^( *[A-Z])/', '${1}.', $row);
	    }
        }
*/
        //replace 、after question symbol to .
        $row = preg_replace('/([0-9]+|第[0-9]+题|< *[0-9]+ *>) *、/', '${1}.', $row);
        //begin with number, no dot followed, and not ) followed.        
        if(preg_match('/^ *[0-9]+.+/', $row)){
            if(!preg_match('/^ *[0-9]+ *?[\.\)-]/', $row)){            
                $row = preg_replace('/^( *[0-9]+)/', '${1}.', $row); 
            }
        }
        
        $arr_new[$j++] = $row;
    }
    return $arr_new;
}

//replace sub string in row level
function pre_treat_rows($arr){
    global $reg_analysis4split;
    $arr_new = array();
    for($i=0; $i < count($arr); $i++){
        $row = $arr[$i];
        //for Non-Breaking Space in HTML 
        $row = preg_replace('/\xC2\xA0/',' ',$row);
        // A B C D
        //1-5 CABAA
        $row = preg_replace('/ *[A-Fa-f] +[A-Fa-f] +[A-Fa-f] +[A-Fa-f]/', '', $row);
        //for answer like:
        //1-5 CABAA
        //5-10 ADBCB
        $row = preg_replace('/ *([0-9]+-[0-9]+)/','答案汇总$1',$row);
        //1B 2B 3D 4B 5A 6B 7C 8B 9D 10C
        //(?:[0-9]+ *\.* *(?:正确|错误|[A-Fa-f√×对错])+ *)(?:[0-9]+ *\.* *(?:正确|错误|[A-Fa-f√×对错])+ *){1,}
        $row = preg_replace('/((?:[0-9]+ *\.* *(?:正确|错误|[A-Fa-fx√×对错])+ *)(?:[0-9]+ *\.* *(?:正确|错误|[A-Fa-fx√×对错])+ *){2,})/','答案$1',$row);
        //if(preg_match('/^ *(?:[0-9]+\.*[A-Fa-f] *)(?:[0-9]+\.*[A-Fa-f] *){1,}(?:[0-9]+\.*[A-Fa-f] *)$/', $row, $reg)){
        //    $row = '答案汇总'.$row;
        //}
        //2.AC
        $row = preg_replace('/^ *([0-9]+ *\.* *[A-Fa-f]+)$/','答案$1',$row);
        //$row = preg_replace('/^ *([0-9]+ *\.* *[A-Fa-f]+)'.$reg_analysis4split.'/','答案$1',$row);
        if(preg_match('/^ *([0-9]+ *\.* *[A-Fa-f]+)'.$reg_analysis4split.'/',$row, $reg)){
            $row = '答案'.$row;
        }
        //for all content of row are blank.
        $row = preg_replace('/^  * $/','',$row);
        //for just only one style of row that have string (单项选择题)
        $row = preg_replace('/\(单项选择题\)/', '', $row);
        //您选择的答案为:[空A-Fa-f]+
        $row = preg_replace('/您选择的答案为 *: *[空A-Fa-f]+/', '', $row);
        //本题得分:0
        $row = preg_replace('/本题得分 *: *[0-9]+/', '', $row);
        //[您的答案]:空
        $row = preg_replace('/\[*您的答案\]* *: *[空A-Fa-f]+/', '', $row);
        //abandon blank row
        if(mb_strlen($row) == 0){
            continue;
        }
        array_push($arr_new , $row);
    }
    return $arr_new;
}
function get_row_type4split($row){
    global $reg_question4role;
    global $reg_option4role;
    global $reg_answer4role;
    global $reg_analysis4role;
    
    global $row_type_question;
    global $row_type_option_only;
    global $row_type_option_answer;
    global $row_type_option_answer_analysis;
    global $row_type_answer_analysis;
    
    global $reg_row_type_question_type1;
    global $reg_row_type_question_type2;
    global $reg_row_type_question;
    global $reg_row_type_option_only;
    global $reg_row_type_option_answer;
    global $reg_row_type_option_answer_analysis;
    global $reg_row_type_answer_analysis;
    $row_type = 0;
    if(preg_match('/'.$reg_answer4role.'/', $row)){
            //echo 'row:'.$row.'<br>';
            //echo '$reg_row_type_answer_analysis:'.$reg_row_type_answer_analysis.'<br>';
        if(preg_match('/'.$reg_row_type_answer_analysis.'/', $row)){  
            $row_type = $row_type_answer_analysis;
        }
    }elseif(preg_match('/'.$reg_option4role.'/', $row)){
            //echo 'row:'.$row.'<br>';
            //echo '$reg_row_type_option_only:'.$reg_row_type_option_only.'<br>';
        if(preg_match('/'.$reg_row_type_option_answer_analysis.'/', $row)){
            $row_type = $row_type_option_answer_analysis;
        }
        else
        if(preg_match('/'.$reg_row_type_option_answer.'/', $row)){
            //echo 'row:'.$row.'<br>';
            //echo '$reg_row_type_option_only:'.$reg_row_type_option_answer.'<br>';
            $row_type = $row_type_option_answer;
        }
        else
        if(preg_match('/'.$reg_row_type_option_only.'/', $row)){
            $row_type = $row_type_option_only;
        }
    }elseif(preg_match('/'.$reg_question4role.'/', $row)){    
            //echo 'row:'.$row.'<br>';
            //echo '$reg_row_type_option_only:'.$reg_row_type_question.'<br>';
            //$row='9.科目期初余额的录入方法是______。 B二级科目上录入 A一级科目上录入 C三级科目上录入 D末级科目上录入 答案:D ';
            //echo '<br>';
            //for($tt=0; $tt<mb_strlen($row); $tt++)
            //{                
            //    echo $tt.':'.ord(mb_substr($row, $tt, 1)).mb_substr($row, $tt, 1).'<br>';
            //}
            if(preg_match('/'.$reg_row_type_question.'/', $row)){
            //echo 'reg_row_type_question<br>';
            $row_type = $row_type_question;
        }
    }
    //echo 'get_row_type4split'.$row.'-['.$row_type.']<br>';
    return $row_type;
}
function split_rows($arr){
    global $reg_question4role;
    global $reg_option4role;
    global $reg_answer4role;
    global $reg_analysis4role;

    set_global_row_type();
    global $row_type_question;
    global $row_type_option_only;
    global $row_type_option_answer;
    global $row_type_option_answer_analysis;
    global $row_type_answer_analysis;
    
    $arr_new = array();
    $pre_row_role = "other";
    $pre_row = "";
    for($i=0; $i < count($arr); $i++){
        $row = $arr[$i];

/*echo 'row1:'.$row.'<br>';
//$row = preg_replace('/\xC2\x0D/',' ',$row);
$row = str_replace("\r","",$row);
echo 'blank code:'.ord(' ').'<br>';
echo 'blank code:'.ord("\r").'<br>';
for($j = 0; $j < mb_strlen($row); $j++){
echo $j.':'.ord(mb_substr($row, $j, 1)).mb_substr($row, $j, 1);
echo '<br>';
}
*/
        switch(get_row_type4split($row))
        {
        	case $row_type_question:
                if(!split_question($row, $arr_new)){
                    array_push($arr_new, $row);
                    echo_error('no need to split row:'.$row, 'split_rows -> row_type_question');
                }
        	    break;
        	case $row_type_option_answer_analysis:
                if(!split_option_answer_analysis($row, $arr_new)){
                    array_push($arr_new, $row);
                    echo_error('no need to split row:'.$row, 'split_rows -> row_type_option_answer_analysis');
                }
        	    break;
        	case $row_type_option_answer:
                if(!split_option_answer($row, $arr_new)){
                    array_push($arr_new, $row);
                    echo_error('no need to split row:'.$row, 'split_rows -> row_type_option_answer_analysis');
                }
        	    break;
        	case $row_type_option_only:
                if(!split_option($row, $arr_new)){
                    array_push($arr_new, $row);
                    echo_error('no need to split row:'.$row, 'split_rows -> row_type_option_only');
                }
        	    break;
        	case $row_type_answer_analysis:
                if(!split_answer_analysis($row, $arr_new)){
                    array_push($arr_new, $row);
                    echo_error('no need to split row:'.$row, 'split_rows -> row_type_answer_analysis');
                }
        	    break;
        	default:
        	    array_push($arr_new, $row);
        	    break;
        }
    }
    //echo '<br>'.'arr_new:'.'<br>';
    //print_r($arr_new);
    return $arr_new;
}
function split_answer_analysis($row, &$arr_new){
    global $reg_answer4role;
    global $reg_answer4split;
    global $reg_analysis4split;
    $pattern = '^ *((?:'.$reg_answer4role.').*?)((?:'.$reg_analysis4split.').*?)';
    $replace = '$1\n$2';
    //echo 'row:'.$row.'<br>';
    //echo 'pattern:'.$pattern.'<br>';
    //echo 'replace:'.$replace.'<br>';
    if(preg_match('/'.$pattern.'/', $row, $reg)){
        //print_r($reg);echo '<br>';
        $row = preg_replace('/'.$pattern.'/', $replace, $row);
        $rows = explode('\n', $row);
        $arr_new = array_merge($arr_new, $rows);
        return true;
    }else{
        return false;
    }
}
function split_option($row, &$arr_new){
    //$reg_option4split = '[A-Fa-f]\.|[0-9]\)|  *[A-Fa-f]|^[A-Fa-f]';
    global $reg_option4split;
    $type = 0;
    $max_cnt = 0;
    if(preg_match_all('/'.$reg_option4split.'/', $row, $reg)){
        if(preg_match_all('/[A-Fa-f]\./', $row, $reg)){
            if($max_cnt < count($reg[0])){
                $max_cnt = count($reg[0]);
                $type = 0;
            }
        }
        if(preg_match_all('/[0-9]\)/', $row, $reg)){
            if($max_cnt < count($reg[0])){
                $max_cnt = count($reg[0]);
                $type = 1;
            }
        }
        if(preg_match_all('/^[A-Fa-f]|  *[A-Fa-f]/', $row, $reg)){
            if($max_cnt < count($reg[0])){
                $max_cnt = count($reg[0]);
                $type = 2;
            }
        }
        if($max_cnt < 2)
        {
            return false;
        }
        //echo '<br><font color="red">max count: '.$max_cnt.'  tpye: '.$type.'</font><br>';
    }
    switch($type)
    {
    case 0:
        $cnt = $max_cnt;
        //$pattern = '^ *([A-Fa-f]\..*?) *([A-Fa-f]\..*?) *([A-Fa-f]\..*?) *([A-Fa-f]\..*?)';
        //$replace = '$1\n$2\n$3\n$4';
        $pattern = '^ *([A-Fa-f]\..*?)';
        $replace = '$1';
        for($i=1; $i<$cnt; $i++){
            $pattern = $pattern.' *([A-Fa-f]\..*?)';
            $replace = $replace.'\n$'.($i+1);
        }
        //echo $pattern.'<br>'.$replace.'<br>';
        //echo 'row: '.$row.'<br>';
        $row = preg_replace('/'.$pattern.'/', $replace, $row);
        //print_r(explode('\n', $row));
    break;
    case 1:
        $cnt = $max_cnt;
        //$pattern = '^ *([0-9]\).*?) *([0-9]\).*?)';
        //$replace = '$1\n$2';
        $pattern = '^ *([0-9]\).*?)';
        $replace = '$1';
        for($i=1; $i<$cnt; $i++){
            $pattern = $pattern.' *([0-9]\).*?)';
            $replace = $replace.'\n$'.($i+1);
        }
        //echo $pattern.'<br>'.$replace.'<br>';
        //echo 'row: '.$row.'<br>';
        $row = preg_replace('/'.$pattern.'/', $replace, $row);
        //print_r(explode('\n', $row));
    break;
    case 2:
        $cnt = $max_cnt;
        //$pattern = '^ *([A-Fa-f].*?) *([A-Fa-f].*?) *([A-Fa-f].*?) *([A-Fa-f].*?)';
        //$replace = '$1\n$2\n$3\n$4';
        $pattern = '^ *([A-Fa-f].*?)';
        $replace = '$1';
        for($i=1; $i<$cnt; $i++){
            $pattern = $pattern.' *([A-Fa-f].*?)';
            $replace = $replace.'\n$'.($i+1);
        }
        //echo $pattern.'<br>'.$replace.'<br>';
        //echo 'row: '.$row.'<br>';
        $row = preg_replace('/'.$pattern.'/', $replace, $row);
        //print_r(explode('\n', $row));
    break;
    }
    $rows = explode('\n', $row);
    if($rows != null){
        $arr_new = array_merge($arr_new, $rows);
        return true;
    }else{
        return false;
    }
    //$row = "A.收款凭证 B.付款凭证 C.转账凭证 D.通用记账凭证 D.通用记账凭证";
    //$row = "A.社会文明水平B.社会经济制度C.市场发达程度D.科技发展水平";
    //$row = "  A.社会文明水平B.社会经济制度 C.社会经济制度 ";
    //$row = "   1)生产职能  3)fd   2)反映B.监督职能";
    //blank before [A-Fa-f] is needed.
    //$row = "A积累性支出 B转移性支出 C补偿性支出 D购买性支出";
}
function split_option_answer_analysis($row, &$arr_new){
    global $reg_question4split;
    global $reg_option4split;    
    global $reg_answer4split;
    global $reg_analysis4split;
    
    $isok = true;

    for($j = 5; $j >= 0; $j--){
        $pattern_match_option='(?:'.$reg_option4split.').*?';
        $pattern_match_post = '(?:'.$reg_answer4split.').*?(?:'.$reg_analysis4split.').*?';
        $pattern_replace_option = '(?:'.$reg_option4split.').*?';
        $pattern_replace_post = '((?:'.$reg_answer4split.').*?)((?:'.$reg_analysis4split.').*?)';
        $replace = '$1\n$2\n$3';
        $pattern_match = '(?:'.$pattern_match_option.'){'.$j.'}'.$pattern_match_post;
        $pattern_replace = '((?:'.$pattern_replace_option.'){'.$j.'})'.$pattern_replace_post;
        //echo 'pattern match: '.$pattern_match.'<br>';
        //echo 'pattern replace: '.$pattern_replace.'<br>';
        if(mb_preg_match_all('/'.$pattern_match.'/', $row, $matches)){
            $opt_cnt = $j;
            //echo '<br>option count: '.$opt_cnt.'<br>';
            //echo 'pattern match: '.$pattern_match.'<br>';
            //echo 'pattern replace: '.$pattern_replace.'<br>';
            break;
        }
    }
    if($opt_cnt > 1)
    {
        $replace = '$1\n$2\n$3';
        $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
        //print_r(explode('\n', $row));
        $rows = explode('\n', $row);
        //array_push($arr_new, $rows[0]);
        split_option($rows[0], $arr_new);
        array_push($arr_new, $rows[1]);
        array_push($arr_new, $rows[2]);
    }
    else
    if($opt_cnt == 1){
        $replace = '$1\n$2\n$3';
        $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
        //print_r(explode('\n', $row));
        $rows = explode('\n', $row);
        array_push($arr_new, $rows[0]);
        array_push($arr_new, $rows[1]);
        array_push($arr_new, $rows[2]);
    }
    else
    /*
    if($opt_cnt == 0){
        //1. 下列各项.不属于专用记账凭证的是()。 [1] => 答案是D. [2] => 本题分析 专用记账凭证包括:收款凭证.付款凭证.转账凭证。
        $pattern_replace = $pattern_replace_pre.$pattern_replace_post;
        $replace = '$1\n$2\n$3';
        $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
        //print_r(explode('\n', $row));
        $rows = explode('\n', $row);
        array_push($arr_new, $rows[0]);
    array_push($arr_new, $rows[1]);
            array_push($arr_new, $rows[2]);
    }
    else
    */
    if($opt_cnt == -1){
        $isok = false;
    }
    return $isok;
}
function split_option_answer($row, &$arr_new){
    global $reg_question4split;
    global $reg_option4split;
    global $reg_answer4split;
    global $reg_analysis4split;

    $isok = true;
    for($j = 5; $j >= 0; $j--){
        $pattern_match_option='(?:'.$reg_option4split.').*?';
        $pattern_match_post = '(?:'.$reg_answer4split.').*?';
        $pattern_replace_option = '(?:'.$reg_option4split.').*?';
        $pattern_replace_post = '((?:'.$reg_answer4split.').*?)';
        $replace = '$1\n$2';
        $pattern_match = '(?:'.$pattern_match_option.'){'.$j.'}'.$pattern_match_post;
        $pattern_replace = '((?:'.$pattern_replace_option.'){'.$j.'})'.$pattern_replace_post;
        //echo 'pattern match: '.$pattern_match.'<br>';
        //echo 'pattern replace: '.$pattern_replace.'<br>';
        if(mb_preg_match_all('/'.$pattern_match.'/', $row, $matches)){
            $opt_cnt = $j;
            //echo '<br>option count: '.$opt_cnt.'<br>';
            //echo 'pattern match: '.$pattern_match.'<br>';
            //echo 'pattern replace: '.$pattern_replace.'<br>';
            break;
        }
    }
    if($opt_cnt > 1)
    {
        $replace = '$1\n$2';
        $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
        //print_r(explode('\n', $row));
        $rows = explode('\n', $row);
        //array_push($arr_new, $rows[0]);
        split_option($rows[0], $arr_new);
        array_push($arr_new, $rows[1]);
    }
    else
    if($opt_cnt == 1){
        $replace = '$1\n$2';
        $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
        //print_r(explode('\n', $row));
        $rows = explode('\n', $row);
        array_push($arr_new, $rows[0]);
        array_push($arr_new, $rows[1]);
    }
    else
    if($opt_cnt == -1){
        $isok = false;
    }
    return $isok;
}
function set_global_row_type(){
    global $reg_question4role;
    global $reg_option4role;
    global $reg_answer4role;
    global $reg_analysis4role;
    
    global $reg_question4split;
    global $reg_option4split;    
    global $reg_answer4split;
    global $reg_analysis4split;

    global $reg_row_type_question_type1;
    global $reg_row_type_question_type2;
    global $reg_row_type_question;
    global $reg_row_type_option_only;
    global $reg_row_type_option_answer;
    global $reg_row_type_option_answer_analysis;
    global $reg_row_type_answer_analysis;
    
    global $row_type_question;
    global $row_type_option_only;
    global $row_type_option_answer;
    global $row_type_option_answer_analysis;
    global $row_type_answer_analysis;
    
    $pattern_question = '(?:'.$reg_question4split.').*?';
    $pattern_options='(?:'.$reg_option4split.').*?';
    $pattern_answer = '(?:'.$reg_answer4split.').*?';
    $pattern_analysis = '(?:'.$reg_analysis4split.').*?';
    //$pattern_match_post = $pattern_answer.$pattern_analysis
    $reg_row_type_question_type1 = $pattern_question.'(?:'.$pattern_options.'){0,5}'.$pattern_answer.$pattern_analysis;
    $reg_row_type_question_type2 = $pattern_question.'(?:'.$pattern_options.'){0,5}'.$pattern_answer;
    $reg_row_type_question = $reg_row_type_question_type1.'|'.$reg_row_type_question_type2;

    $reg_row_type_option_only = '(?:'.$pattern_options.'){2,}';
    $reg_row_type_option_answer = '(?:'.$pattern_options.'){0,5}'.$pattern_answer;
    $reg_row_type_option_answer_analysis = '(?:'.$pattern_options.'){0,5}'.$pattern_answer.$pattern_analysis;

    $reg_row_type_answer_analysis = '(?:'.$reg_answer4role.').*?'.$pattern_analysis;
    
    $row_type_question = 10;
    $row_type_option_answer_analysis = 21;
    $row_type_option_answer = 22;
    $row_type_option_only = 23;
    $row_type_answer_analysis = 30;
}
function split_question($row, &$arr_new){
    global $reg_question4split;
    global $reg_option4split;    
    global $reg_answer4split;
    global $reg_analysis4split;
    $pattern_match_pre = '(?:'.$reg_question4split.').*?';
    $pattern_match_post = '(?:'.$reg_answer4split.').*?(?:'.$reg_analysis4split.').*?';
    $pattern_match_option='(?:'.$reg_option4split.').*?';
    $pattern_match_type1 = $pattern_match_pre.'(?:'.$pattern_match_option.'){0,5}'.$pattern_match_post;
    $pattern_match_pre = '(?:'.$reg_question4split.').*?';
    $pattern_match_post = '(?:'.$reg_answer4split.').*?';
    $pattern_match_option='(?:'.$reg_option4split.').*?';
    $pattern_match_type2 = $pattern_match_pre.'(?:'.$pattern_match_option.'){0,5}'.$pattern_match_post;
    
    $exam_array = array();
    if(mb_preg_match_all('/'.$pattern_match_type1.'|'.$pattern_match_type2.'/', $row, $matches)){
        $question_cnt = count($matches);
        for($j = 0; $j < $question_cnt - 1; $j++){
            $start = $matches[$j];
            $length = $matches[$j+1] - $start;
            array_push($exam_array, mb_substr($row, $start, $length));
            //echo 'start:'.$start.' - length:'.$length.'<br>';
        }
        $start = $matches[$j];
        $length = mb_strlen($row) - $start;
        //echo 'start:'.$start.' - length:'.$length.'<br>';
        array_push($exam_array, mb_substr($row, $start, $length));
    }
    //echo '<font color="red">exam_array: </font><br>';
    //print_r($exam_array);echo '<br>';
    
    $isok = true;
    if(count($exam_array) == 0){
        $isok=false;
    }
    else{
        for($i=0; $i < count($exam_array); $i++){
            $row = $exam_array[$i];
            if(split_question_type1($row, $arr_new)){
                continue;
            }
            if(split_question_type2($row, $arr_new)){
                continue;
            }
            $isok=false;
        }
    }
    return $isok;
}
function split_question_type1($row, &$arr_new){
    global $reg_question4split;
    global $reg_option4split;    
    global $reg_answer4split;
    global $reg_analysis4split;
    //$reg_question4split = ' *[0-9]+.*?\.| *第[0-9]+题[:.]| *第[0-9]+题.?';
    //$reg_option4split = '[A-Fa-f][\.:]|[0-9]\)|  *[A-Fa-f]|^[A-Fa-f]';
    //$reg_answer4split = '(?:[0-9]+\.)*\[*(?:参考答案|正确答案|标准答案|正确答案|答案|本题正确答案为|答案)\]*.*?[A-Fa-f√×T]';
    //$reg_analysis4split = '\[*(?:试题解析|参考解析|答案解析|本题分析|试题点评|本题来源|本题考点|本题解析|解析)\]*.+?';
    $isok = true;
    $opt_cnt = -1;
    //echo 'row:'.$row.'<br>';
    for($j = 5; $j >= 0; $j--){
        $pattern_match_pre = '(?:'.$reg_question4split.').*?';
        $pattern_match_post = '(?:'.$reg_answer4split.').*?(?:'.$reg_analysis4split.').*?';
        $pattern_match_option='(?:'.$reg_option4split.').*?';
        $pattern_replace_pre = '((?:'.$reg_question4split.').*?)';
        $pattern_replace_post = '((?:'.$reg_answer4split.').*?)((?:'.$reg_analysis4split.').*?)';
        $pattern_replace_option = '(?:'.$reg_option4split.').*?';
        $replace = '$1\n$2\n$3\n$4';
        $pattern_match = $pattern_match_pre.'(?:'.$pattern_match_option.'){'.$j.'}'.$pattern_match_post;
        $pattern_replace = $pattern_replace_pre.'((?:'.$pattern_replace_option.'){'.$j.'})'.$pattern_replace_post;
        //echo 'pattern match: '.$pattern_match.'<br>';
        //echo 'pattern replace: '.$pattern_replace.'<br>';
        if(mb_preg_match_all('/'.$pattern_match.'/', $row, $matches)){
            $opt_cnt = $j;
            //echo '<br>option count: '.$opt_cnt.'<br>';
            //echo 'pattern match: '.$pattern_match.'<br>';
            //echo 'pattern replace: '.$pattern_replace.'<br>';
            break;
        }
    }
    if($opt_cnt > 1)
    {
        $replace = '$1\n$2\n$3\n$4';
        $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
        //print_r(explode('\n', $row));
        $rows = explode('\n', $row);
        array_push($arr_new, $rows[0]);
        split_option($rows[1], $arr_new);
        array_push($arr_new, $rows[2]);
        array_push($arr_new, $rows[3]);
    }elseif($opt_cnt == 1){
        $replace = '$1\n$2\n$3\n$4';
        $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
        //print_r(explode('\n', $row));
        $rows = explode('\n', $row);
        array_push($arr_new, $rows[0]);
        array_push($arr_new, $rows[1]);
        array_push($arr_new, $rows[2]);
        array_push($arr_new, $rows[3]);
    }elseif($opt_cnt == 0){
    //1. 下列各项.不属于专用记账凭证的是()。 [1] => 答案是D. [2] => 本题分析 专用记账凭证包括:收款凭证.付款凭证.转账凭证。
        $pattern_replace = $pattern_replace_pre.$pattern_replace_post;
        $replace = '$1\n$2\n$3';
        $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
        //print_r(explode('\n', $row));
        $rows = explode('\n', $row);
        array_push($arr_new, $rows[0]);
        array_push($arr_new, $rows[1]);
        array_push($arr_new, $rows[2]);
    }elseif($opt_cnt == -1){
        $isok = false;
    }
    return $isok;
}
function split_question_type2($row, &$arr_new){
    global $reg_question4split;
    global $reg_option4split;
    global $reg_answer4split;
    global $reg_analysis4split;
    $isok = true;
    $opt_cnt = -1;
    for($j = 5; $j >= 0; $j--){
        $pattern_match_pre = '(?:'.$reg_question4split.').*?';
        $pattern_match_post = '(?:'.$reg_answer4split.').*?';
        $pattern_match_option='(?:'.$reg_option4split.').*?';
        $pattern_replace_pre = '((?:'.$reg_question4split.').*?)';
        $pattern_replace_post = '((?:'.$reg_answer4split.').*?)';
        $pattern_replace_option = '(?:'.$reg_option4split.').*?';        
        $replace = '$1\n$2\n$3';
        $pattern_match = $pattern_match_pre.'('.$pattern_match_option.'){'.$j.'}'.$pattern_match_post;
        $pattern_replace = $pattern_replace_pre.'((?:'.$pattern_replace_option.'){'.$j.'})'.$pattern_replace_post;
        if(mb_preg_match_all('/'.$pattern_match.'/', $row, $matches)){
            $opt_cnt = $j;
            //echo '<br>row:'.$row.'<br>';
            //echo '<br>option count:'.$opt_cnt.'<br>';
            //echo 'pattern match:'.$pattern_match.'<br>';
            //echo 'pattern replace:'.$pattern_replace.'<br>';
            break;
        }
    }
    if($opt_cnt > 1)
    {
        $replace = '$1\n$2\n$3';
        $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
        //print_r(explode('\n', $row));
        $rows = explode('\n', $row);
        array_push($arr_new, $rows[0]);
        split_option($rows[1], $arr_new);
        array_push($arr_new, $rows[2]);
    }elseif($opt_cnt == 1){
        $replace = '$1\n$2\n$3';
        $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
        //print_r(explode('\n', $row));
        $rows = explode('\n', $row);
        array_push($arr_new, $rows[0]);
        array_push($arr_new, $rows[1]);
        array_push($arr_new, $rows[2]);
    }elseif($opt_cnt == 0){
        $pattern_replace = $pattern_replace_pre.$pattern_replace_post;
        $replace = '$1\n$2';
        $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
        //print_r(explode('\n', $row));
        $rows = explode('\n', $row);
        array_push($arr_new, $rows[0]);
        array_push($arr_new, $rows[1]);
    }elseif($opt_cnt == -1){
        $isok = false;
    }
    return $isok;
}
function insert_preserve_row($arr, $j, $prow){
    
}
function get_row_by_index($arr, $i){
    if(array_key_exists($i, $arr)){
        return $arr[$i];
    }else{
        return '';
    }    
}
//blank is delete while merge rows.
function merge_rows($arr){
    $arr_new = array();
    $cur_row = '';
    $next_row = '';
    $cur_role = '';
    $next_role = '';
    $j=0;
    $preserve_row = '';
    for($i=0; $i < count($arr); $i++){
        //space in option is preserved
        if(get_role_by_row($arr[$i]) == 'option'){
            continue;
        }else{
            $arr[$i] = str_replace(' ', '', $arr[$i]);
        }
    }
    for($i=0; $i < count($arr)-1; $i++){
        //$cur_row = $arr[$i];
        //$next_row = $arr[$i+1];
        //' ' is reserved for option recognize, and is no use for row merge.
        $cur_row = $arr[$i];
        $next_row = $arr[$i+1];
        $next_next_row = get_row_by_index($arr, $i+2);
        $cur_role = get_role_by_row($cur_row);
        $next_role = get_role_by_row($next_row);
        //echo 'in merge rows:'.$cur_row.'['.$cur_role.']<br>';
        //如果本行是unknown，不论下一行是否unknown，将这两行合并，并将preserve_row加入arr_new.
        if($cur_role == "unknown"){
            if($next_role == "unknown"){                
                //if next row and next-next row can merge, it will be considered first.
                //1.
                //<1>、
                //【答案】 A
                if(get_role_by_row($next_row.$next_next_row) != "unknown"){
                    if($preserve_row != ''){
                        $arr_new[$j++] = $preserve_row;
                   	    echo_notice('insert preserve_row:'.$preserve_row, 'merge_rows1');
                        $preserve_row = '';
                    }
                    $preserve_row = $preserve_row.$next_row.$next_next_row;
                    $i+=2;                    
                }
                else                
                if(get_role_by_row($cur_row.$next_row) != "unknown"){
                    if($preserve_row != ''){
                        $arr_new[$j++] = $preserve_row;
                   	    echo_notice('insert preserve_row:'.$preserve_row, 'merge_rows1');
                        $preserve_row = '';
                    }
                    $preserve_row = $preserve_row.$cur_row.$next_row;
                    $i++;
                }else{
                    $preserve_row = $preserve_row.$cur_row;
                }
            }else{
                //$preserve_row is inserted before known row.
            	if(get_role_by_row($cur_row.$next_row) != "unknown")
            	{
            		if($preserve_row != ''){
                        $arr_new[$j++] = $preserve_row;
                        echo_notice('insert preserve_row:'.$preserve_row, 'merge_rows2');
                        $preserve_row = '';
            		}
            		//$arr_new[$j++] = $cur_row.$next_row;
            		$preserve_row = $preserve_row.$cur_row.$next_row;
            		$i++;
            	}
                /*
            	//答案:
				//A2.B
            	if(($next_role == "option") && (get_role_by_row($cur_row.$next_row == "answer")))
            	{
            		if($preserve_row != ''){
            			$arr_new[$j++] = $preserve_row;
                    	echo_error('unknown row:'.$preserve_row, 'merge_rows');
            		}
            		$arr_new[$j++] = $cur_row.$next_row;
            		$preserve_row = '';
            		$i++;
            	}
            	*/
            	/*
            	else
            	//141.
            	//[答案]：C
            	if(($next_role == "answer") && (get_role_by_row($cur_row.$next_row == "answer"))){
            	    if($preserve_row != ''){
            	        $arr_new[$j++] = $preserve_row;
            	        echo_error('unknown row:'.$preserve_row, 'merge_rows');
            	    }
            	    $arr_new[$j++] = $cur_row.$next_row;
            	    $preserve_row = '';
            	    $i++;
            	}*/
            	else{
            		if($preserve_row != ''){
                        $arr_new[$j++] = $preserve_row.$cur_row;
                        echo_notice('insert preserve_row:'.$preserve_row, 'merge_rows2');
                        $preserve_row = '';
            		}
            		//$arr_new[$j++] = $cur_row.$next_row;
            		//$preserve_row = $preserve_row.$next_row;
            	}
            }
            /*
            echo '<br><br>';
            echo 'current row:'.$cur_row.'['.$cur_role.']<br>';
            echo 'next row:'.$next_row.'['.$next_role.']<br>';
            echo 'merge role: '.$cur_row.$next_row.'['.get_role_by_row($cur_row.$next_row).']<br>';
            echo 'preserve_row:'.$preserve_row.'<br>';
            */
        }
        else
        //rows[unknown|analysis] follow analysis row will be add to current analysis.
        //[解析]：甲企业应纳税额=10000×60%×4=24000(元)
        //乙企业应纳税额=10000×40%×4=16000(元)
        //[该题针对“城镇土地使用税应纳税额的计算”知识点进行考核]
        //148.
        if(($cur_role == 'analysis') || ($cur_role == 'question') || ($cur_role == 'option')){
            //echo '$cur_row'.$cur_row.'<br>';
            $rows_follow_analysis = $cur_row;
            $find_rows = false;
            //if next row and next-next row can merge, it will be considered first.
            while(($next_role == 'unknown') && (get_role_by_row($next_row.$next_next_row) == "unknown")){
            	if($preserve_row != ''){
            		$arr_new[$j++] = $preserve_row;
                   	echo_notice('insert preserve_row:'.$preserve_row, 'merge_rows3');
            	    $preserve_row = '';
            	}
            	$rows_follow_analysis = $rows_follow_analysis.'\n'.$next_row;
            	//$arr_new[$j++] = $cur_row.$next_row;
            	$find_rows = true;
            	$i++;
            	if($i >= count($arr)){
            	    break;
            	}
                $next_row = get_row_by_index($arr, $i+1);
                $next_next_row = get_row_by_index($arr, $i+2);
                $next_role = get_role_by_row($next_row);
                //echo $next_row.'['.$next_role.']'.'<br>';
            }
            if($find_rows){
                $arr_new[$j++] = $rows_follow_analysis;
                //$rows_follow_analysis = $cur_row;
                $find_rows = false;
            }else{
                if($preserve_row != ''){
            		$arr_new[$j++] = $preserve_row;
                   	echo_notice('insert preserve_row:'.$preserve_row, 'merge_rows4');
            	    $preserve_row = '';
            	}
                $arr_new[$j++] = $rows_follow_analysis;                
            }
            /*
            echo '<br><br>';
            echo 'arrive here. merge'.$rows_follow_analysis.'['.$cur_role.']<br>';
            echo 'current row:'.$cur_row.'['.$cur_role.']<br>';
            echo 'next row:'.$next_row.'['.$next_role.']<br>';
            echo 'length:'.strlen($arr[$i]).'<br>';
            echo 'preserve_row:'.$preserve_row.'<br>';
            */
        }
        else
        {
            if($preserve_row != ''){
            	//if can merge current row and preserve row(rows above)
                //if(get_role_by_row($preserve_row.$cur_row) != "unknown"){
                //    $arr_new[$j++] = $preserve_row.$cur_row;
                //    $preserve_row='';
                //}else{
                    $arr_new[$j++] = $preserve_row;
                    echo_notice('insert preserve_row:'.$preserve_row, 'merge_rows5');
                    $preserve_row = $arr[$i];
                //}
            }else{
            	//merge current row and next row.
            	//following case not need merge.
                //D.港口的码头用地
                //141.
                //[答案]：C&& (get_role_by_row($cur_row.$next_row) != "unknown")
            	if(($next_role == "unknown")  &&
    						(get_role_by_row($next_row.$next_next_row) == "unknown")){           		
                	$arr_new[$j++] = $arr[$i].$arr[$i+1];
                	$i++;
            	}else{
            		$arr_new[$j++] = $cur_row;//$arr[$i];
            	}
            }
            /*
            echo '<br><br>';
            echo 'arrive here. insert'.$arr[$i].'['.$cur_role.']<br>';
            echo 'current row:'.$cur_row.'['.$cur_role.']<br>';
            echo 'next row:'.$next_row.'['.$next_role.']<br>';
            echo 'length:'.strlen($arr[$i]).'<br>';
            echo 'preserve_row:'.$preserve_row.'<br>';
            */
        }
    }
    //echo '<br><br>';
    //echo 'i:'.$i.'<br>';
    //echo 'count($arr):'.count($arr).'<br>';
    if($i < count($arr)){
	    $cur_row = $arr[$i];
	    $cur_role = get_role_by_row($cur_row);
        //echo 'current row:'.$cur_row.'['.$cur_role.']<br>';
	    if($cur_role != "unknown"){
	        if($preserve_row != ''){
	            $arr_new[$j++] = $preserve_row;
	        }
	        $arr_new[$j++]  = $cur_row ;
	    }else{
	        $preserve_row = $preserve_row.$cur_row;
	        $arr_new[$j++] = $preserve_row;
	    }
    }else{
    	if($preserve_row != ''){
    		$arr_new[$j++] = $preserve_row;
    	}
    }
    return $arr_new;
}
function post_treat_rows(&$arr){
    $arr_new = array();
    for($i=0; $i < count($arr); $i++){
        $row = $arr[$i];
        //for just only one style of row that have string (单项选择题)
        $row = preg_replace('/\(单项选择题\)/', '', $row);
        //您选择的答案为:[空A-Fa-f]+
        $row = preg_replace('/您选择的答案为 *: *[空A-Fa-f]+/', '', $row);
        //本题得分:0
        $row = preg_replace('/本题得分 *: *[0-9]+/', '', $row);
        //[您的答案]:空
        $row = preg_replace('/\[*您的答案\]* *: *[空A-Fa-f]+/', '', $row);
        //[单选][多选][判断]
        $row = preg_replace('/(\[ *(?:单 *选|多 *选|判 *断) *\]) *$/', '', $row);
        //abandon blank row
        if(mb_strlen($row) == 0){
            continue;
        }
        array_push($arr_new , $row);
    }
    return $arr_new;
}
function find_split_answer($arr, $start, $end, &$content_pre, &$role_pre) {
    global $reg_answer4replace;
    global $reg_analysis4replace;
    $search = array ();
    $replace = array ();
    array_push ( $search, '\( *([A-Fa-fT]+) *\)' ); // √×
    array_push ( $replace, '()' );
    array_push ( $search, '([A-Fa-fT]+)$' ); // √×
    array_push ( $replace, '' );
    for($i = $start; $i < $end; $i ++) {
        $row_tmp = $arr [$i];
        // echo 'in find and split answer, $row_tmp'.$row_tmp.'<br>';
        // print_r($content_pre);
        // echo '<br><br>';
        // ' ' is reserved for option recognize, and is no use for role recognize.
        // get error while preg_match_all for character √×
        $role_tmp = get_role_by_row ( $arr [$i] );
        // echo $row_tmp.$role_tmp.'<br>';
        switch ($role_tmp) {
        case 'question' :
            $row_tmp = preg_replace ( '/(√|正确|对)/', 'T', $row_tmp );
            $row_tmp = preg_replace ( '/(×|错误|错)/', 'F', $row_tmp );
            $find_answer = false;
            for($j = 0; $j < count ( $search ); $j ++) {
                // echo '√'.ord('√').'<br>';
                if (preg_match_all ( '/' . $search [$j] . '/', $row_tmp, $reg )) {
                    // it is not right if more than one pattern are found by reg
                    if (count ( $reg [0] ) > 1) {
                        echo_error ( 'There are more than one answer in question.', 'get_and_check_roles' );
                        continue;
                    }
                    array_push ( $content_pre, preg_replace ( '/' . $search [$j] . '/', $replace [$j], $row_tmp ) );
                    array_push ( $role_pre, $role_tmp );
                    $answers_tmp = $reg [1] [0]; // '答案'.$reg[count($reg) - 1];
                    for($k = 0; $k < mb_strlen ( $answers_tmp ); $k ++) {
                        array_push ( $content_pre, $answers_tmp [$k] );
                        array_push ( $role_pre, "answer" ); // get_role_by_row($answers_tmp)
                    }
                    $find_answer = true;
                    break;
                    // print_r($reg);
                    // echo '<br>'.preg_replace('/'.$search[$j].'/', $replace[$j], $row).'<br>';
                }
            }
            if (! $find_answer) {
                array_push ( $content_pre, $arr [$i] );
                array_push ( $role_pre, $role_tmp );
            }
            break;
        case 'answer' :
            // echo 'row_tmp:'.$row_tmp.'<br>';
            $row_tmp = preg_replace ( '/' . $reg_answer4replace . '/', '', $row_tmp );
            $row_tmp = preg_replace ( '/(√|正确|对)/', 'T', $row_tmp );
            $row_tmp = preg_replace ( '/(×|x|错误|错)/', 'F', $row_tmp );
            // case1:the row begin with 答案汇总 means that is a group of answer.
            if (preg_match ( '/^答案汇总/', $row_tmp, $matches )) {
                // if(preg_match('/[0-9]+-[0-9]+[A-Fa-f]+/', $row_tmp)){
                if (mb_preg_match_all ( '/[A-Fa-fT]/', $row_tmp, $matches )) { // √×
                                                                            // echo '<br>row_tmp:'.$row_tmp;
                                                                            // echo '<br>matches:';print_r($matches);
                    for($k = 0; $k < count ( $matches ); $k ++) {
                        array_push ( $content_pre, mb_substr ( $row_tmp, $matches [$k], 1 ) );
                        // array_push($content_pre, $row_tmp[$matches[$k]]);
                        array_push ( $role_pre, $role_tmp );
                    }
                } else {
                    if (mb_strlen ( $row_tmp ) > 0) {
                        array_push ( $content_pre, $row_tmp );
                        array_push ( $role_pre, $role_tmp );
                    }
                }
                continue;
            } 
            else 
            if (preg_match ( '/(?:[0-9]+ *\.* *(?:[A-Fa-fT]+) *){2,}/', $row_tmp, $matches )){
                // echo 'in function find and split answer.<br>';
                $max_cnt = 12;
                $j = $max_cnt;
                if (preg_match ( '/(?:[0-9]+ *\.* *(?:[A-Fa-fT]+) *){2,}/', $row_tmp, $matches )) {
                    for(; $j > 1; $j --) {
                        if (preg_match ( '/(?:[0-9]+ *\.* *(?:[A-Fa-fT]+) *){' . $j . '}/', $row_tmp, $matches )) {
                            break;
                        }
                    }
                }
                if ($j == $max_cnt) {
                    echo_error ( 'not found answer seperated by index.', 'find_split_answer 2' );
                } else {
                    $one_case = '(?:[0-9]+ *\.* *([A-Fa-fT]+) *)';
                    $pattern = '^.*?' . $one_case;
                    $replace = '$1';
                    for($k = 1; $k < $j; $k ++) {
                        $pattern = $pattern . $one_case;
                        $replace = $replace . '\n$' . ($k + 1);
                    }
                    // echo 'row:'.$row_tmp.'<br>';
                    // echo 'pattern:'.$pattern.'<br>';
                    $row_tmp = preg_replace ( '/' . $pattern . '/', $replace, $row_tmp );
                    $row_tmp = explode ( '\n', $row_tmp );
                    // print_r($row_tmp);
                    for($k = 0; $k < count ( $row_tmp ); $k ++) {
                        array_push ( $content_pre, $row_tmp [$k] );
                        array_push ( $role_pre, $role_tmp );
                    }
                }
            }else {
                if (mb_preg_match_all ( '/[A-Fa-fT]/', $row_tmp, $matches )) {
                    // echo '<br>row_tmp:'.$row_tmp;
                    // echo '<br>matches:';print_r($matches);
                    for($k = 0; $k < count ( $matches ); $k ++) {
                        array_push ( $content_pre, mb_substr ( $row_tmp, $matches [$k], 1 ) );
                        // array_push($content_pre, $row_tmp[$matches[$k]]);
                        array_push ( $role_pre, $role_tmp );
                    }
                } else {
                    if (mb_strlen ( $row_tmp ) > 0) {
                        array_push ( $content_pre, $row_tmp );
                        array_push ( $role_pre, $role_tmp );
                    }
                }
            }
            break;
        case 'analysis' :
            $row_tmp = preg_replace ( '/' . $reg_analysis4replace . '/', '', $row_tmp );
            array_push ( $content_pre, $row_tmp );
            array_push ( $role_pre, $role_tmp );
            break;
        default :
            array_push ( $content_pre, $row_tmp );
            array_push ( $role_pre, $role_tmp );
            break;
        }
    }
}
function get_and_check_roles(&$arr){
    global $global_role2index;
    global $reg_answer4replace;
    global $reg_analysis4replace;
    $role_pre = array();
    $content_pre = array();
    $role_line_pre = '';
    for($i = 0; $i < count($arr); $i++){
        $role_line_pre = $role_line_pre.$global_role2index[get_role_by_row($arr[$i])];
    }
    //echo '$role_line_pre'.$role_line_pre.'<br>';
    //for($i = 0; $i < count($arr); $i++){
    //    $role_pre[$i] = get_role_by_row($arr[$i]);
    //}
    //return $role_pre;
    
    /*多个题目*/
    $p0 = '(?:12{4,5}){2,}(?:3+?4*){2,}';
    //$p1 = '12{4,5}[4]*3[4]*';
    $pattern_array = array();
    $pattern_array[0] = $p0;
    //$pattern_array[1] = $p1;
    for($i=0; $i<count($pattern_array); $i++)
    {
        if($i == 0){
            $match = $pattern_array[$i];
        }else{
            $match = $match.'|'.$pattern_array[$i];
        }
    }
    $match = '/'.$match.'/';
    $prev_start_pos = 0;
    $prev_line_length = 0;
    if(preg_match_all($match, $role_line_pre, $reg, PREG_OFFSET_CAPTURE)){
        for($j=0; $j<count($reg[0]); $j++){
            $role_line_tmp = $reg[0][$j][0];
            $start_pos = $reg[0][$j][1];
            //如果前一个被识别的字符串开始位置和长度之和不等于下一个字符串开始位置，说明中间有未被识别的字符串，试用默认方式分割
            if(($prev_start_pos + $prev_line_length) != $start_pos ){
                find_split_answer($arr, $prev_start_pos + $prev_line_length, $start_pos, $content_pre, $role_pre);
            }
            $prev_start_pos = $start_pos;
            $prev_line_length = strlen($role_line_tmp);
            //echo '$prev_start_pos'.$prev_start_pos.' - $prev_line_length'.$prev_line_length.'<br>';

            //whether the count of question and answer is the same.
            $cnt_same = false;
            if(preg_match_all('/12{4,5}/', $role_line_tmp, $reg_q_o, PREG_OFFSET_CAPTURE)
            && preg_match_all('/3+?4*/', $role_line_tmp, $reg_a_a, PREG_OFFSET_CAPTURE)){
                if(count($reg_q_o[0]) == count($reg_a_a[0])){
                    echo_notice('count of question and option is different from answer and analysis.', 'get_and_check_roles');
                    $cnt_same = true;
                }
            }
            if($cnt_same){
                //被识别的字符串，答案不进行分割。
                for($i = $start_pos; $i < ($start_pos+$prev_line_length); $i++){
                    $row_tmp =  $arr[$i];
                    $role_tmp = get_role_by_row($arr[$i]);
                    if($role_tmp == 'answer'){
                        $row_tmp = preg_replace('/'.$reg_answer4replace.'/', '', $row_tmp);
                        $row_tmp = preg_replace('/(√|正确|对)/','T',$row_tmp);
                        $row_tmp = preg_replace('/(×|x|错误|错)/','F',$row_tmp);
                        //echo '$row_tmp:'.$row_tmp.'<br>';
                        if(mb_preg_match_all('/[A-Fa-fT]/', $row_tmp, $matches)){
                            $str_tmp = '';
                            for($k=0; $k<count($matches); $k++){
                                $str_tmp = $str_tmp.mb_substr($row_tmp, $matches[$k], 1);
                            }
                            if(mb_strlen($row_tmp) > 0){
                                array_push($content_pre, $str_tmp);
                                array_push($role_pre, $role_tmp);
                            }
                        }else{
                            if(mb_strlen($row_tmp) > 0){
                                array_push($content_pre, $row_tmp);
                                array_push($role_pre, $role_tmp);
                            }                            
                        }
                    }else
                    if($role_tmp == 'analysis'){
                        //echo '$row_tmp in get_and_check_roles:'.$row_tmp.'<br>';
                        $row_tmp = preg_replace ( '/' . $reg_analysis4replace . '/', '', $row_tmp );
                        array_push ( $content_pre, $row_tmp );
                        array_push ( $role_pre, $role_tmp ); 
					}else{
                        array_push($content_pre, $row_tmp);
                        array_push($role_pre, $role_tmp);
                    }
                }
            }else{
                find_split_answer($arr, $start_pos, $start_pos+$prev_line_length, $content_pre, $role_pre);
            }
        }
    }
    if($prev_start_pos + $prev_line_length < count($arr)){
        find_split_answer($arr, $prev_start_pos + $prev_line_length, count($arr), $content_pre, $role_pre);
    }
    $arr = $content_pre;
    return $role_pre;
}

//$global_role2index['question'] = 1;
//$global_role2index['option'] = 2;
//$global_role2index['answer'] = 3;
//$global_role2index['analysis'] = 4;
//$global_role2index['unknown'] = 5;
//$reg_question4role = '^ *[0-9]+.*?\..+|^ *第[0-9]+题';
function array_post_treat($arr){
    global $reg_question4replace;
    global $reg_option4replace;
    global $reg_answer4replace;
    global $reg_analysis4replace;
    $search = array('A', 'B', 'C', 'D', 'E', 'F', 'a', 'b', 'c', 'd', 'e', 'f', '√', '×');
    $replace = array('1', '2', '3', '4', '5', '6', '1', '2', '3', '4', '5', '6', 'T', 'F');
    
    //global $answer_in_question_multi_choice;
    //global $answer_in_question_single_choice;
    //global $multi_choice;
    //global $single_choice;
    global $option_less_than_two;
    //global $true_or_false_question;
    //global $fill_in_the_blank;
    //global $separate_answer_analysis_from_question;
    //global $separate_answer_from_question;
    //global $type_array;    

    global $type_multi_choice;
    global $type_single_choice;
    global $type_true_or_false_question;
    global $type_fill_in_the_blank;
    
    $type = $arr['type'];
    if(array_key_exists('option', $arr)){
        if(count($arr['option'])<2){
            $type = $option_less_than_two;
        }
    }
    //echo '<br>type'.$type.':';print_r($arr);
    if(array_key_exists('question', $arr)){
        $arr['question'] = preg_replace('/'.$reg_question4replace.'/', '', $arr['question']);
        //preg_replace may create new blank row.
        $array_tmp = explode('\n', $arr['question']);
        $length_tmp = count($array_tmp);
        if($length_tmp > 1){
            $arow_tmp = '';
            $arr['question'] = '';
            for($j = 0; $j < $length_tmp; $j++){
                $arow_tmp = $array_tmp[$j];
                if(mb_strlen($arow_tmp) == 0){
                    continue;
                }
                $arr['question'] = $arr['question'].$arow_tmp.'\n';
            }
        }
    }
    if(array_key_exists('answer', $arr)){
        if($type != $option_less_than_two){
            $arr['answer'] = str_replace($search, $replace, $arr['answer']);            
        }else{
            $arr['answer'] = strtoupper($arr['answer']);
            if(array_key_exists('option', $arr)){
                for($i=0; $i<count($arr['option']); $i++){
                    if(preg_match('/'.$arr['answer'].'/', $arr['option'][$i])){
                        if(preg_match('/'.'正确'.'/', $arr['option'][$i])){
                            $arr['answer'] = 'T';
                        }
                        else
                        if(preg_match('/'.'错误'.'/', $arr['option'][$i])){
                            $arr['answer'] = 'F';
                        }                    
                    }
                }
            }
        }
    }
	if(array_key_exists('option', $arr)){
		if ($type != $option_less_than_two) {
			$opt_array_tmp = $arr['option'];
			asort($opt_array_tmp);
			// print_r($opt_array_tmp);
			// print_r($arr['option']);
			$i = 0;
			foreach($opt_array_tmp as $key => $value){
				// echo $key.'->'.$value.'<br>';
				$arr['option'][$i++] = preg_replace( '/' . $reg_option4replace . '/', '', $value );
			}
		}
		else {
			$arr ['option'] = array();
		}
	}
    if(array_key_exists('analysis', $arr)){
        //echo 'analysis:'.$arr['analysis'].'<br>';

		//has replace in get_and_check_roles and file_split_answer.
        //$arr['analysis'] = preg_replace('/'.$reg_analysis4replace.'/', '', $arr['analysis']);

        //preg_replace may create new blank row.
        $array_tmp = explode('\n', $arr['analysis']);
        $length_tmp = count($array_tmp);
        if($length_tmp > 1){
            $arow_tmp = '';
            $arr['analysis'] = '';
            for($j = 0; $j < $length_tmp; $j++){
                $arow_tmp = $array_tmp[$j];
                if(mb_strlen($arow_tmp) == 0){
                    continue;
                }
                //echo $j.'-'.$arow_tmp.'<br>';
                if(($length_tmp-1) != $j){
                    $arr['analysis'] = $arr['analysis'].$arow_tmp.'\n';
                }else{
                    $arr['analysis'] = $arr['analysis'].$arow_tmp;
                }
            }
        }
    }
    //guess array types
    if(count($arr['option']) == 0){
        if(preg_match_all('/[FT]/', $arr['answer'], $matches)){            
            $arr['type'] = $type_true_or_false_question;
        }else{          
            $arr['type'] = $type_fill_in_the_blank;                
        }
    }else{
        if(mb_strlen($arr['answer']) == 1){
            $arr['type'] = $type_single_choice;
        }else{
            $arr['type'] = $type_multi_choice;
        }
    }
    //echo '<br>';
    //echo '$arr<br>';
    //print_r($arr);
    //echo 'count:'.count($arr['option']).'<br>';
    return $arr;
}

function rows_to_array($arr, $arr_role){
//echo '<br>';
//print_r($arr);
//echo '<br>';
//echo '<br>';
//print_r($arr_role);
    global $answer_in_question_multi_choice;
    global $answer_in_question_single_choice;
    global $separate_answer_from_question;
    global $multi_choice;
    global $single_choice;
    global $option_less_than_two;
    global $true_or_false_question;
    global $fill_in_the_blank;
    //global $separate_answer_analysis_from_question;
    global $type_array;
    
    global $global_role2index;
    global $global_index2role;

    $roles_line = '';
    for($i=0; $i<count($arr_role); $i++){
        $role_tmp = $arr_role[$i];
        //if($role_tmp != 'unknown'){
            $roles_line = $roles_line.$global_role2index[$role_tmp];
        //}
    }
    //echo $roles_line;
    
    /*答案在题目后面*/
    //6、答案在题目中-多选：问题、1一个以上答案、[4|5]个选项、[解析](要在类型7前面)
    //如：1332222
    $p0 = '13{2,}2{4,5}4*';
    //7、答案在题目中-单选：问题、答案、[4|5]选项、[解析](要在类型3前面)
    //如：132222
    $p1 = '132{4,5}4*';

    /*多个题目*/
    //4、单选：题目与答案分开；问题、4个选项。答案解析在一块。(要在类型2前面)
    //如：122221222212222343434
    //reg:'(?:12{4}){2,}(?:34){2,}';
    $p2 = '(?:12{0,5}){2,}(?:3+?4*){2,}';//
    //5、单选：题目与答案分开；问题、4个选项。只有答案无解析。
    //122221222212222122221222212222122221222212222122223333333333
    //reg:'(?:12{4}){2,}3{2,}';

    /*单个题目*/
    //2、多选：问题、[4|5]个选项、1个以上答案，[解析]可以在答案前面或后面(要在题型1前面)
    //如：122222333122222334
    $p3 = '12{4,5}[4]*3{2,}[4]*';
    //1、单选：问题、[4|5]个选项、答案、[解析]可以在答案前面或后面
    //如：122223
    $p4 = '12{4,5}[4]*3[4]*';
    //3、判断或填空：问题、少于2个选项、答案、[解析]可以在答案前面或后面
    //如：1223
    $p5 = '12{0,2}[4]*3[4]*';

    $pattern_array = array();
    $pattern_array[0] = $p0;
    $pattern_array[1] = $p1;
    $pattern_array[2] = $p2;
    $pattern_array[3] = $p3;
    $pattern_array[4] = $p4;
    $pattern_array[5] = $p5;
    //$pattern_array[6] = $p6;
    //collect elements of pattern_array to $match for preg_match_all.
    for($i=0; $i<count($pattern_array); $i++)
    {
        if($i == 0){
            $match = $pattern_array[$i];
        }else{
            $match = $match.'|'.$pattern_array[$i];
        }
    }
    $match = '/'.$match.'/';
    //echo $roles_line.'<br>';
    //echo $match.'<br>';
    
    //$reg_type=0
    $exam_array = array();
    $exam_cur = array();
    //$array_queue = array();
    //used to record start and length of recognize line string.
    $prev_start_pos = 0;
    $prev_line_length = 0;
    if(preg_match_all($match, $roles_line, $reg, PREG_OFFSET_CAPTURE)){
        for($j=0; $j<count($reg[0]); $j++){
            //echo '<br>';
            //print_r($reg[0]);
            //echo '<br>';
            $role_line_tmp = $reg[0][$j][0];
            $start_pos = $reg[0][$j][1];
            //如果前一个被识别的字符串开始位置和长度之和不等于下一个字符串开始位置，说明中间有未被失败的字符串。
            if(($prev_start_pos + $prev_line_length) != $start_pos ){
                echo_error('role line between '.($prev_start_pos + $prev_line_length).' and '.$start_pos.' is not recognized.', 'rows_to_array');
            }
            for($reg_type=0; $reg_type<count($pattern_array); $reg_type++)
            {
                if(preg_match('/^'.$pattern_array[$reg_type].'$/', $role_line_tmp))
                {
                    break;
                }
            }
            $prev_start_pos = $start_pos;
            $prev_line_length = strlen($role_line_tmp);
            /*
            echo '<br>';
            echo $j.': <br>';
            echo 'recognized string: ';
            print_r($role_line_tmp);
            echo '<br>start pos：'.$start_pos;
            echo '<br>string length:'.strlen($role_line_tmp);
            echo '<br>type: '.$type_array[$reg_type].': '.$pattern_array[$reg_type].'<br>';            
            echo '<br>';
            */

            switch($reg_type){
            case $answer_in_question_multi_choice:
            case $answer_in_question_single_choice:
            case $multi_choice:
            case $single_choice:
            case $option_less_than_two:
                $exam_cur = array();
                //useful, can not comment
                $exam_cur['type'] = $reg_type;
                $exam_cur['option'] = array();
                $exam_cur['answer'] = '';
                $exam_cur['analysis'] = '';
                for($k=0; $k<strlen($role_line_tmp); $k++){
                    $role_tmp = $global_index2role[$role_line_tmp[$k]];
                    //echo 'role_tmp'.$role_tmp.'<br>';
                    if($role_tmp == 'option'){
                        array_push($exam_cur[$role_tmp], $arr[$start_pos+$k]);
                    }                    
                    else
                    //more analysis may be exist, merge them.
                    if($role_tmp == 'analysis'){
                        $exam_cur['analysis'] = $exam_cur['analysis'].'\n'.$arr[$start_pos+$k];
                        //print_r($exam_cur['analysis']);
                    }
                    elseif(($role_tmp == 'answer')){
                        $exam_cur[$role_tmp] = $exam_cur[$role_tmp].$arr[$start_pos+$k];
                    }else{
                        $exam_cur[$role_tmp] = $arr[$start_pos+$k];
                    }
                }
                array_push($exam_array, array_post_treat($exam_cur));
            break;
            case $separate_answer_from_question:
                //echo '$role_line_tmp'.$role_line_tmp.'<br>';
                if(preg_match_all('/12{0,5}/', $role_line_tmp, $reg_q_o, PREG_OFFSET_CAPTURE) 
                && preg_match_all('/3+?4*/', $role_line_tmp, $reg_a_a, PREG_OFFSET_CAPTURE)){

                    //echo '<br>split type 4<br>';
                    //print_r($reg_q_o[0]);
                    //echo '<br>';
                    //print_r($reg_a_a[0]);
                    //echo '<br>';
                    if(count($reg_q_o[0]) != count($reg_a_a[0])){
                        echo_error('count of question and option is different from answer and analysis.', 'split_rows2array');
                        continue;
                    }
                }else
                {
                    echo_error('find substring of '.$role_line_tmp.' fail.', 'split_rows2array');
                    continue;
                }
                for($k = 0; $k < count($reg_q_o[0]); $k++){
                    $exam_cur = array();
                    $exam_cur['type'] = $reg_type;
                    $exam_cur['option'] = array();
                    $exam_cur['answer'] = '';
                    $exam_cur['analysis'] = '';
                    $line_tmp = $reg_q_o[0][$k][0];
                    $start_pos_tmp =  $reg_q_o[0][$k][1];
                    for($m=0; $m<strlen($line_tmp); $m++){
                        $role_tmp = $global_index2role[$line_tmp[$m]];
                        //echo 'role_tmp'.$role_tmp.'<br>';
                        if($role_tmp == 'option'){
                            array_push($exam_cur[$role_tmp], $arr[$start_pos+$start_pos_tmp+$m]);
                        }                  
                        else
                        //more analysis may be exist, merge them.
                        if($role_tmp == 'analysis'){
                            $exam_cur['analysis'] = $exam_cur['analysis'].'\n'.$arr[$start_pos+$start_pos_tmp+$m];
                            //print_r($exam_cur['analysis']);
                        }
                        else{
                            $exam_cur[$role_tmp] = $arr[$start_pos+$start_pos_tmp+$m];
                        }
                    }
                    $line_tmp = $reg_a_a[0][$k][0];
                    $start_pos_tmp =  $reg_a_a[0][$k][1];
                    for($m=0; $m<strlen($line_tmp); $m++){
                        $role_tmp = $global_index2role[$line_tmp[$m]];
                        //echo 'role_tmp'.$role_tmp.'<br>';
                        if($role_tmp == 'option'){
                            array_push($exam_cur[$role_tmp], $arr[$start_pos+$start_pos_tmp+$m]);
                        }                  
                        else
                        //more analysis may be exist, merge them.
                        if($role_tmp == 'analysis'){
                            $exam_cur['analysis'] = $exam_cur['analysis'].'\n'.$arr[$start_pos+$start_pos_tmp+$m];
                            //print_r($exam_cur['analysis']);
                        }
                        else{
                            $exam_cur[$role_tmp] = $arr[$start_pos+$start_pos_tmp+$m];
                        }
                    }
        	    
                    array_push($exam_array, array_post_treat($exam_cur));
                }
                //echo '<br>split type 4<br>';
                //print_r($reg_q_o[0]);
                //echo '<br>';
                //print_r($reg_a_a[0]);
                //echo '<br>';            
            break;
            }
        }
    }
    //else{
    //    $exam_array=array();
    //}
    return $exam_array;
}
//$global_role2index['question'] = 1;
//$global_role2index['option'] = 2;
//$global_role2index['answer'] = 3;
//$global_role2index['analysis'] = 4;
//$global_role2index['unknown'] = 5;
//$answer_in_question_multi_choice = 0;
//$answer_in_question_single_choice = 1;
//$separate_answer_analysis_from_question = 2;
//$separate_answer_from_question = 3;
//$multi_choice = 4;
//$single_choice = 5;
//$true_or_false_question = 6;
function preview_objective_exam_stage1($exam_array){
    echo '<div class="demo-box">';
    echo '<div class="header">';
    echo '<h2>试题浏览</h2>';//preview_results
    echo '<div class="view_result"><a href=\'parser-exam.php?type=array\'>查看Array格式</a></div>';
    echo '</div>';
    //echo '$length of exam-array'.count($exam_array).'<br>';
    for($i=0; $i < count($exam_array); $i++){
        $cur_exam = $exam_array[$i];
        output_a_exam($cur_exam, $i);
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
//$type_name_array = array('单选', '多选', '判断', '填空');
function output_a_exam($cur_exam, $index){
	global $type_multi_choice;
	global $type_single_choice;
	global $type_true_or_false_question;
	global $type_fill_in_the_blank;
	global $type_name_array;
	$array_tmp = array();
	$exam_type = '未知';
	$option_prefix = array('A', 'B', 'C', 'D', 'E', 'F');
	
    switch($cur_exam['type'])
    {
	    case $type_single_choice:
	    	$exam_type = '单选';
	    	break;
	    case $type_multi_choice:
	    	$exam_type = '多选';
	    	break;
	    case $type_true_or_false_question:
	    	$exam_type = '判断';
	    	break;
	    case $type_fill_in_the_blank:
	    	$exam_type = '填空';
	    	break;
    }
    
    if(array_key_exists('question', $cur_exam)){
        $array_tmp = explode('\n', $cur_exam['question']);
        $length_tmp = count($array_tmp);
        if($length_tmp > 1){
            echo '<div class=question>'.($index+1).'、';
            for($j=0; $j<$length_tmp-1; $j++){
                echo ''.$array_tmp[$j].'<br>';
            }
            echo $array_tmp[$j].'<span class=type>['.$exam_type.']</span><br>';
            echo '</div>';
        }else{
            echo '<div class=question>';
            echo ($index+1).'、'.$array_tmp[0];
            echo '<span class=type>['.$exam_type.']</span>';
            echo '</div>';
        }
    }
    if(array_key_exists('option', $cur_exam)){
        for($j=0; $j<count($cur_exam['option']); $j++){
            $array_tmp = explode('\n', $cur_exam['option'][$j]);
            $length_tmp = count($array_tmp);
            if($length_tmp > 1){
                echo '<div class=option>'.$option_prefix[$j].'.';
                for($k=0; $k<$length_tmp; $k++){
                    echo $array_tmp[$k].'<br>';
                }
                echo '</div>';
            }else{
                echo '<div class=option>';
                echo $option_prefix[$j].'.'.$array_tmp[0];
                echo '</div>';                    
            }
        }
    }
    if(array_key_exists('answer', $cur_exam)){
        $length_tmp = mb_strlen($cur_exam['answer']);
        echo '<div class=answer>'.'答案：';
        if($type_true_or_false_question == $cur_exam['type']){
            //for($j=0; $j<$length_tmp; $j++){
                echo $cur_exam['answer'];//[$j];
            //}                
        }else
        if($type_fill_in_the_blank == $cur_exam['type']){
                echo $cur_exam['answer'];
        }
        else{
            for($j=0; $j<$length_tmp; $j++){
                //echo $cur_exam['answer'].'<br>';
                echo $option_prefix[$cur_exam['answer'][$j]-1];
            }
        }
        echo '</div>';
    }
    if(array_key_exists('analysis', $cur_exam)){
        $array_tmp = explode('\n', $cur_exam['analysis']);
        $length_tmp = count($array_tmp);
        //echo 'length_tmp'.$length_tmp.'<br>';
        //print_r($length_tmp);
        if($length_tmp > 1){
            echo '<div class=analysis>解析：';
            for($j=0; $j<$length_tmp; $j++){
                echo ''.$array_tmp[$j].'<br>';
            }
            echo '</div>';
        }else
        if($length_tmp == 1 && (mb_strlen($array_tmp[0]) != 0))
        {
            echo '<div class=analysis>';
            echo '解析：'.$array_tmp[0].'<br>';
            echo '</div>';
        }
    }    
}

function output_objective_exam_array($arr)
{
    echo '<div class="demo-box">';
    echo '<div class="header">';
    echo '<h2>数组浏览</h2>';
    echo '<div class="view_result"><a href=\'parser-exam.php?type=review\'>返回</a></div>';
    echo '</div>';
    for($i=0; $i < count($arr); $i++){
        echo '<br>';
        print_r($arr[$i]);
        echo '<br>';
    }
    echo '</div>';
}

function preview_objective_exam_stage2($exam_array){
    global $type_multi_choice;
    global $type_single_choice;
    global $type_true_or_false_question;
    global $type_fill_in_the_blank;

    $single_choice_array = array();
    $multi_choice_array = array();
    $true_or_false_question_array = array();
    $fill_in_blank_array = array();
    $unknown_array = array();
    //echo '<br>exam array:<br>';
    //print_r($exam_array);
    for($i=0; $i < count($exam_array); $i++){
        $cur_exam = $exam_array[$i];
        switch($cur_exam['type'])
        {
        	case $type_single_choice:
        	    array_push($single_choice_array, $cur_exam);
        	    break;
        	case $type_multi_choice:
        	    array_push($multi_choice_array, $cur_exam);
        	    break;
        	case $type_true_or_false_question:
        	    array_push($true_or_false_question_array, $cur_exam);
        	    break;
        	case $type_fill_in_the_blank:
        	    array_push($fill_in_blank_array, $cur_exam);
        	    break;
        	default:
        	    array_push($unknown_array, $cur_exam);
        	    break;
        }
    }
    echo '<div class="demo-box">';
    echo '<div class="header">';
    echo '<h2>试题浏览</h2>';
    echo '<div class="view_result"><a href=\'parser-exam.php?type=array\'>查看Array格式</a></div>';
    echo '</div>';
    for($i=0; $i < count($single_choice_array); $i++){
        $cur_exam = $single_choice_array[$i];
        output_a_exam($cur_exam, $i);
    }
    for($i=0; $i < count($multi_choice_array); $i++){
        $cur_exam = $multi_choice_array[$i];
        output_a_exam($cur_exam, $i);
    }
    for($i=0; $i < count($true_or_false_question_array); $i++){
        $cur_exam = $true_or_false_question_array[$i];
        output_a_exam($cur_exam, $i);
    }
    for($i=0; $i < count($fill_in_blank_array); $i++){
        $cur_exam = $fill_in_blank_array[$i];
        output_a_exam($cur_exam, $i);
    }
    for($i=0; $i < count($unknown_array); $i++){
        $cur_exam = $unknown_array[$i];
        output_a_exam($cur_exam, $i);
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
    //return $exam_array;
}
?>
