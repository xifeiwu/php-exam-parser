<?php
require_once('output_html.php');
do_html_header("parser-index");
$reg_question4role = '^ *[0-9]+\.|^ *第[0-9]+题';
$reg_option4role = '^ *[A-F]|^ *[0-9]\)';
$reg_answer4role = '^参考答案|^正确答案|^[0-9]+\.答案|^答案';
$reg_analysis4role = '^试题解析|^参考解析|^答案解析|^本题分析|^试题点评|^本题来源|^本题考点|^解析';

$reg_question4split = '[0-9]+ *\.|第 *[0-9]+ *题';
$reg_option4split = '[A-F]\.|[0-9]\)|  *[A-F]|^[A-F]';
$reg_answer4split = '参考答案|正确答案|[0-9]+\.答案|答案';
$reg_analysis4split = '试题解析|参考解析|答案解析|本题分析|试题点评|本题来源|本题考点|解析';
function split_answer_analysis(){
    $row = "1.【答案解析】D。解析:会计核算的基本方法包括设置会计科目和账户、复式记账、填制和审核会计凭证、登记账簿、成本计算、财产清查和编制财务报表七>种方法，编制财务预算属于管理会计的范畴，不包括在会计核算的方法中。";
    $row = preg_replace('/ *【(.*)】/', '$1', $row);
    global $reg_answer4split;
    global $reg_analysis4split;
    echo 'row: '.$row.'<br><br>';

    $preg = $reg_answer4split.'|'.$reg_analysis4split;
    echo 'preg_match_all pattern: '.$preg.'<br>';
    if(preg_match('/'.$reg_answer4split.'|'.$reg_analysis4split.'/', $row, $reg)){
        print_r($reg[0]);
    }
    echo '<br><br>';

    $preg = $reg_answer4split.'|'.$reg_analysis4split;
    echo 'preg_match_all pattern: '.$preg.'<br>';
    if(preg_match_all('/'.$reg_answer4split.'|'.$reg_analysis4split.'/', $row, $reg)){
        print_r($reg[0]);
    }
    echo '<br><br>';

    $preg = '('.$reg_answer4split.').+('.$reg_analysis4split.')'; 
    echo 'pattern: '.$preg.'<br>';
    if(preg_match('/'.$preg.'/', $row, $reg)){
        print_r($reg);
    }
    echo '<br><br>';

    $pattern = '^ *((?:'.$reg_answer4split.').*?)((?:'.$reg_analysis4split.').*?)';
    $replace = '$1\n$2';
    echo 'pattern:  '.$pattern.'<br>';
    echo 'replace: '.$replace.'<br>';
    $rows = preg_replace('/'.$pattern.'/', $replace, $row);
    print_r(explode('\n', $rows));
    echo '<br><br>';

    $preg = '^ *((?:'.$reg_answer4split.').*?).+((?:'.$reg_analysis4split.').*?)';
    echo 'pattern: '.$preg.'<br>';
    if(preg_match('/'.$preg.'/', $row, $reg)){
        print_r($reg);
    }
    echo '<br><br>';

}
function echo_error($error, $func){
    echo '<font color="red">Error in function'.$func.': '.$error.'</font><br>';
}
//split_answer_analysis();
function mb_preg_match_all($pattern, $subject, &$matches){
    if(preg_match_all($pattern, $subject, $regs)){
        $str_find = $regs[0];
        $start = 0;
        $matches = array();
        for($i = 0; $i < count($str_find); $i++){
            $str = $str_find[$i];
            $start = mb_strpos($subject, $str, $start);
            $matches[$i] = $start;
        }
        return true;
    }else{
        return false;
    }
}
function test_reg(){
    global $reg_question4split;
    global $reg_option4split;
    $row = "9.科目期初余额的录入方法是______。  B二级科目上录入  A一级科目上录入      C三级科目上录入   D末级科目上录入 答案.D  ";
    for($j = 5; $j >= 0; $j--){
        $pattern_match_pre = '[0-9]+\..*?';
        $pattern_match_post = '答案.*?';
        $pattern_match_option='('.$reg_option4split.').*?';
        $pattern_replace_pre = '([0-9]+\..*?)';
        $pattern_replace_post = '(答案.*?)';
        $pattern_replace_option = '(?:'.$reg_option4split.').*?';
        $replace = '$1\n$2\n$3';
        $pattern_match = $pattern_match_pre.'('.$pattern_match_option.'){'.$j.'}'.$pattern_match_post;
        $pattern_replace = $pattern_replace_pre.'((?:'.$pattern_replace_option.'){'.$j.'})'.$pattern_replace_post;
        if(mb_preg_match_all('/'.$pattern_match.'/', $row, $matches)){
            $opt_cnt = $j;
            echo '<br>row: '.$row.'<br>';
            echo '<br>option count: '.$opt_cnt.'<br>';
            echo 'pattern match: '.$pattern_match.'<br>';
            echo 'pattern replace: '.$pattern_replace.'<br>';
            break;
        }
    }
}
//test_reg();
function test_multi_array(){
    $data = array();
    $ele1 = array();
    $ele1['question']='que2';
    $ele2 = array();
    $i=0;
    $data['q'.$i]=$ele1;
    $data['q2']=$ele2;
    $data[0] = 'hello';
    print_r($data);
    if(array_key_exists($i, $data)){
        print_r($data);
    }else{
        echo 'not recognize.';
    }
}
//test_multi_array();
function get_str(){
    $role[0] = "role0";
    return $role;
}
function test_array_return(){
    $arrs = array();
    $arrs[1] = 'array value 1';
    echo  gettype(get_str()).'<br>';
    array_push($arrs, get_str());
    print_r($arrs);
    echo '<br>length of arr 1'.strlen($arrs[1]).'<br>';
}
//test_array_return();
function test_string_index(){
$string = 'abcd';
echo $string[2];
echo $string{2};
echo substr($string, 2, 1);
}
//test_string_index();
function reg_get_answer(){
//    $row = "23、财产清查的内容不包括( A )。";
    $row = "2、ascii码是一种（）A";
    $search = array();
    $replace = array();
    array_push($search, '\( *([A-F]) *\)');
    array_push($replace, '()');
    array_push($search, '([A-F])$');
    array_push($replace, '');
    for($j = 0; $j < count($search); $j++){
        if(preg_match('/'.$search[$j].'/', $row, $reg)){
            //print_r($reg);
            echo '<br>'.$reg[count($reg) - 1].'<br>';
            echo '<br>'.preg_replace('/'.$search[$j].'/', $replace[$j], $row).'<br>';
        }
    }
}
//reg_get_answer();
function reg_find_answer(){
$row = '1-5 CABAA';
$row = preg_replace('/ *([0-9]+-[0-9]+)/','答案$1',$row);
echo 'row:'.$row.'<br>';
}
//reg_find_answer();
function array_queue(){
    $arr_tmp = array('a');
    $arr_tmp['option'] = array();
    array_push($arr_tmp['option'], 'option');
    array_push($arr_tmp, 'b');
    array_push($arr_tmp, 'c');
    array_push($arr_tmp, 'd');
    print_r($arr_tmp);
    array_shift($arr_tmp);
    print_r($arr_tmp);
    array_shift($arr_tmp);
    print_r($arr_tmp);
}
//array_queue();
function reg_find_unregular(){
    $row = '1222212222122221222234343434';
    $p = '12{4}3[4]*|(?:12{4}){2,}(?:34){2,}';//
    if(preg_match_all('/'.$p.'/', $row, $reg, PREG_OFFSET_CAPTURE)){
        print_r($reg);
    }
}
//reg_find_unregular();
$answer_in_question_multi_choice = 0;
$answer_in_question_single_choice = 1;
$separate_answer_analysis_from_question = 2;
$separate_answer_from_question = 3;
$multi_choice = 4;
$single_choice = 5;
$true_or_false_question = 6;
$type_array = array(
'answer_in_question_multi_choice', 'answer_in_question_single_choice',
'separate_answer_analysis_from_question', 'separate_answer_from_question',
'multi_choice', 'single_choice', 'true_or_false_question',
);
function reg_find_to_split_roles(){
    $row = "13222241222212222341222212222122223434341312222341222234122223412222122221222212222122221222212222122221222212222122223333333333";
    global $answer_in_question_multi_choice;
    global $answer_in_question_single_choice;
    global $multi_choice;
    global $single_choice;
    global $true_or_false_question;
    global $separate_answer_analysis_from_question;
    global $separate_answer_from_question;
    global $type_array;
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
    $p2 = '(?:12{4,5}){2,}(?:3+4*){2,}';//'(?:12{4}){2,}(?:34){2,}';
    //5、单选：题目与答案分开；问题、4个选项。只有答案无解析。
    //122221222212222122221222212222122221222212222122223333333333
    $p3 = '(?:12{4}){2,}3{2,}';

    /*单个题目*/
    //2、多选：问题、[4|5]个选项、1个以上答案，[解析]可以在答案前面或后面(要在题型1前面)
    //如：122222333122222334
    $p4 = '12{4,5}[4]*3{2,}[4]*';
    //1、单选：问题、[4|5]个选项、答案、[解析]可以在答案前面或后面
    //如：122223
    $p5 = '12{4,5}[4]*3[4]*';
    //3、判断：问题、少于2个选项、答案、[解析]可以在答案前面或后面
    //如：1223
    $p6 = '12{0,2}[4]*3[4]*';

    $pattern_array = array();
    $pattern_array[0] = $p0;
    $pattern_array[1] = $p1;
    $pattern_array[2] = $p2;
    $pattern_array[3] = $p3;
    $pattern_array[4] = $p4;
    $pattern_array[5] = $p5;
    $pattern_array[6] = $p6;
    for($i=0; $i<count($pattern_array); $i++)
    {
        if($i == 0){
            $match = $pattern_array[$i];
        }else{
            $match = $match.'|'.$pattern_array[$i];
        }
    }
    $match = '/'.$match.'/';
    echo '<br>'.$row.'<br>';
    $prev_start_pos = 0;
    $prev_line_length = 0;
    if(preg_match_all($match, $row, $reg, PREG_OFFSET_CAPTURE)){
        for($j=0; $j<count($reg[0]); $j++){
            $role_line_tmp = $reg[0][$j][0];
            $start_pos = $reg[0][$j][1];
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
            echo '<br>';
            echo $j.': <br>';
            echo 'recognized string: ';
            print_r($role_line_tmp);
            echo '<br>start pos：'.$start_pos;
            echo 'string length:'.strlen($role_line_tmp);
            echo '<br>type: '.$type_array[$reg_type].': '.$pattern_array[$reg_type].'<br>';
            echo '<br>';
        }
    }
}
//reg_find_to_split_roles();
function reg_split_roles_new_type(){
    $row = '122221222212222122221222221222223434343434343';
    $p = '(?:12{4,5}){2,}(?:3+4*){2,}';
    $p1 = '12{4,5}';
    $p2 = '3+?4*';
    if(preg_match_all('/'.$p.'/', $row, $reg, PREG_OFFSET_CAPTURE)){
        if(preg_match_all('/'.$p1.'/', $row, $reg, PREG_OFFSET_CAPTURE)){
            echo '<br>';
            print_r($reg);
        }
        if(preg_match_all('/'.$p2.'/', $row, $reg, PREG_OFFSET_CAPTURE)){
            echo '<br><br>';
            print_r($reg);
        }        
    }
}
//reg_split_roles_new_type();
function reg_find_answer_in_question(){
    $row_tmp = '2、区分人类历史上不同社会形态的根本标志是(CD)。';
    $search = array();
    $replace = array();
    array_push($search, '\( *([A-F]+) *\)');
    array_push($replace, '()');
    array_push($search, '([A-F]+)$');
    array_push($replace, '');
    for($j = 0; $j < count($search); $j++){
    echo 'row:'.$row_tmp.'<br>';
    echo 'reg:'.$search[$j].'<br>';
        if(preg_match_all('/'.$search[$j].'/', $row_tmp, $reg)){
            echo 'after replace:'.preg_replace('/'.$search[$j].'/', $replace[$j], $row_tmp);
            print_r($reg);
            //array_push($role_pre, $role_tmp);
            //$content2add = $reg[count($reg) - 1];;//'答案'.$reg[count($reg) - 1];
            //array_push($content_pre, $content2add);
            //array_push($role_pre, "answer");//get_role_by_row($content2add)
            echo '<br>';
            //echo '<br>'.preg_replace('/'.$search[$j].'/', $replace[$j], $row).'<br>';
        }
    }
}
/*
results of preg_match_all:
row:2、区分人类历史上不同社(AB)会形态的根本标志是(CD)。
reg:\( *([A-F]+) *\)
after replace:2、区分人类历史上不同社()会形态的根本标志是()。Array ( [0] => Array ( [0] => (AB) [1] => (CD) ) [1] => Array ( [0] => AB [1] => CD ) ) 
row:2、区分人类历史上不同社(AB)会形态的根本标志是(CD)。
reg:([A-F])$
results of preg_match:
row:2、区分人类历史上不同社(AB)会形态的根本标志是(CD)。
reg:\( *([A-F]+) *\)
after replace:2、区分人类历史上不同社()会形态的根本标志是()。Array ( [0] => (AB) [1] => AB ) 
row:2、区分人类历史上不同社(AB)会形态的根本标志是(CD)。
reg:([A-F])$
*/
//reg_find_answer_in_question();
function testhuiche(){
    $row_tmp = '2、区分人类历史上不同社会形态的根本标志是(CD)。';
    $row = '';
    for($i=0; $i<5; $i++){
        $row = $row.$i.$row_tmp.'\n';
    }
    echo $row;
}
//testhuiche();
function test_preg_replace(){
    $row = '1. . 下列各项，不属于专用记账凭证的是()。 A.收款凭证 B.付款凭证 C.转账凭证 D.通用记账凭证 A B C D 答案是D， 本题分析 专用记账凭证包括:收款凭证.付款凭证.转账凭证。 2 . 下列原始凭证属于自制原始凭证的是()。 A.购入材料后，验收入库时填写的收料单 B.增值税专用发票 C.机票 D.支付过桥费的收费收据 A B C D 答案是A， 本题分析 自制原始凭证在单位内部使用。';
    echo 'row:'.$row.'<br>';
    $row = preg_replace('/ *[A-F] *[A-F] *[A-F] *[A-F]/', '', $row);
    echo 'after row:'.$row.'<br>';
}
//test_preg_replace();
function test_preg_match_chinese(){
    $row = '答案汇总1-5CA答案汇总BA答案汇总A';
    if(preg_match_all('/答案汇总/', $row, $matches)){
        print_r($matches);
        echo 'match';
    }else{
        echo 'not match';
    }
}
//test_preg_match_chinese();
function find_answer_by_index(){
    $row_tmp = '答案:答案汇总1.BD2.AB3.x';
    $row_tmp = preg_replace('/(√|正确|对)/','T',$row_tmp);
    $row_tmp = preg_replace('/(×|x|错误|错)/','F',$row_tmp);
    $max_cnt = 12;
    $j=$max_cnt;
    if(preg_match('/(?:[0-9]+ *\.* *(?:[A-FT]+) *){2}/', $row_tmp, $matches)){
        for(; $j>1; $j--){
            if(preg_match('/(?:[0-9]+ *\.* *(?:[A-FT]+) *){'.$j.'}/', $row_tmp, $matches)){
                echo '<br>matches:';
                print_r($matches);
                echo '<br>';
                break;
            }
        }
    }
    if($j == $max_cnt){
        echo 'not found<br>';
    }else{
        $one_case = '(?:[0-9]+ *\.* *([A-FT]+) *)';
        $pattern = '^.*?'.$one_case;
        $replace = '$1';
        for($k=1; $k<$j; $k++){
            $pattern = $pattern.$one_case;
            $replace = $replace.'\n$'.($k+1);
        }
        echo 'row:'.$row_tmp.'<br>';
        echo 'pattern:'.$pattern.'<br>';
        echo '$replace:'.$replace.'<br>';
        
        $row_tmp = preg_replace('/'.$pattern.'/', $replace, $row_tmp);
        echo '<br>';
        print_r($row_tmp);
        echo '<br>';
        $row_tmp = explode('\n', $row_tmp);
        echo '<br>';
        print_r($row_tmp);
        echo '<br>';
    }
}
//find_answer_by_index();
function test_row_type(){
    $row='9.科目期初余额的录入方法是______。 B二级科目上录入 A一级科目上录入 C三级科目上录入 D末级科目上录入 答案:D ';
    $reg='(?: *[0-9]+.*?\.| *第[0-9]+题[:.]| *第[0-9]+题.?).*?(?:(?:[A-F][\.:]|[0-9]\)| *[A-F]|^[A-F]).*?){0,5}(?:(?:[0-9]+\.)*\[*(?:参考答案|正确答案|标准答案|正确答案|答案|本题正确答案为|答案及解析|答案)\]*.*?[A-F√×T]).*?(?:\[*(?:试题解析|参考解析|答案解析|本题分析|试题点评|本题来源|本题考点|本题解析|解析)\]*(:.+?|[^:].+?)).*?|(?: *[0-9]+.*?\.| *第[0-9]+题[:.]| *第[0-9]+题.?).*?(?:(?:[A-F][\.:]|[0-9]\)| *[A-F]|^[A-F]).*?){0,5}(?:(?:[0-9]+\.)*\[*(?:参考答案|正确答案|标准答案|正确答案|答案|本题正确答案为|答案及解析|答案)\]*.*?[A-F√×T]).*?';
    
        if(preg_match('/'.$reg.'/', $row)){
            echo 'reg_row_type_question<br>';
        }else{
            echo 'no pattern';
        }
}
//test_row_type();
function replace_option_prefix(){
    $row = '1).该公司12月份应交的所得税税额为(    )元。  （第一个小题）';
    if(preg_match('/^ *([0-9]+)\) *\./', $row, $reg)){
        print_r($reg);
    }
    echo '<br>';
    //^( *([0-9]+)\) *\.)
    //Array ( [0] => 1). [1] => 1). [2] => 1 )
    $row = preg_replace('/^ *([0-9]+)\) *\./', '$1.', $row);
    echo 'row:'.$row.'<br>';
}
replace_option_prefix();
do_html_footer();
?>
