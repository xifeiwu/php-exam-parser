<?php
mb_internal_encoding('UTF-8');
function test_mbstring(){
    mb_internal_encoding('UTF-8');
    //测试时文件的编码方式要是UTF8  
    $str='中文a字1符';  
    echo strlen($str).'<br>';//14  
    echo mb_strlen($str,'utf8').'<br>';//6  
    echo mb_strlen($str,'gbk').'<br>';//8  
    echo mb_strlen($str,'gb2312').'<br>';//10  
    echo mb_internal_encoding();
}
function test_string_split()
{
$row = "1.下列各项，不属于专用记账凭证的是()。A.收款凭证B.付款凭证C.转账凭证D.通用记账凭证(单项选择题)ABCD答案是D，本题分析专用记账凭证包括:收款凭证.付款凭证.转账凭证。";
$start=55;
$length = 69 - $start;
echo $row.' - '.mb_strlen($row).'<br>';
echo $start." - ".$length.'<br>';
echo mb_substr($row, $start, $length).'<br>';
}
function test_preg_match_all()
{
    $row = "1.下列各项，不属于专用记账凭证的是()。A.收款凭证B.付款凭证C.转账凭证D.通用记账凭证(单项选择题)ABCD答案是D，本题分析专用记>账凭证包括:收款凭证.付款凭证.转账凭证。";
    if(preg_match_all('/[0-9][0-9]*\.|[A-Z]\./', $row, $regs)){
        print_r($regs);
        $str_find = $regs[0];
        $start = 0;
        $matches = array();
        for($i = 0; $i < count($str_find); $i++){
            $str = $str_find[$i];
            $start = mb_strpos($row, $str, 0);
            $matches[$i] = $start;
        }
        echo '<br>'.$row.'<br>';
        print_r($matches);
        for($i = 0; $i < count($matches) - 1; $i++){
            $start = $matches[$i];
            $length = $matches[$i+1] - $start;
            echo mb_substr($row, $start, $length).'<br>';
        }
        $start = $matches[$i];
        $length = mb_strlen($row) - $start;
            echo mb_substr($row, $start, $length).'<br>';
    }
}
//test_preg_match_all();
$reg = '^[0-9][0-9]*\.';
function test_global_var(){
    global $reg;
    echo $reg.'<br>';
}
//test_global_var();
function test_preg_replace(){
//$string = 'April 15, 2003';
//$pattern = '/(\w+) (\d+), (\d+)/i';
//$replacement = '${1}1,$3';
//echo preg_replace($pattern, $replacement, $string);
    $row="42423开始";
    if(preg_match('/^[0-9]+[^\.]/', $row)){
echo "in function".'<br>';
        $row = preg_replace('/^([0-9]+)/', '${1}.', $row);
    }
echo "after: ".$row.'<br>';
}
//test_preg_replace();
function test_parameter_pass(&$arr){
   array_push($arr, '1');
   array_push($arr, '2');
   array_push($arr, '3');
}
function call_function(){
$arr_tmp=array();
test_parameter_pass($arr_tmp);
print_r($arr_tmp);
}
//call_function();
function test_switch(){
    $role=array('question', 'answer');
    for($i=0; $i<count($role);$i++){
        switch($role[$i])
		{
		case 'question':
		echo 'question is contained.'.'<br>';
		break;
		case 'answer':
		echo 'answer is contained.'.'<br>';
		break;
		default:
		echo 'default is contained.'.'<br>';
		break;
		}
	}
}
//test_switch();
function test_mularray(){
$question = array();
$question['question'] = 'questions';
$question['option'][0]='A.aa';
$question['option'][1]='B.aa';
$question['option'][2]='C.aa';
$question['option'][3]='D.aa';
$question['answer'][0] = 'A';
$question['answer'][1] = 'C';
print_r($question);
}
//test_mularray();
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
function test_mb_preg_match_all(){
    $row = '答案: B.D';
    mb_preg_match_all('/[A-F]/', $row, $matches);
    print_r($matches);
    for($i=0;$i<count($matches);$i++){
        echo mb_substr($row, $matches[$i], 1).'<br>';
    }
}
//test_mb_preg_match_all();
function split_option(){
    //dot after [A-F] is needed.
    $row = "1. 下列各项，不属于专用记账凭证的是（）。 A收款凭证 2)付款凭证 C.转账凭证 D.通用记账凭证 (单项选择题) A   B   C   D    答案是D. 本题分析  专用记账凭证包括：收款凭证、付款凭证、转账凭证。 2.  下列原始凭证属于自制原始凭证的是（）。 A.入材料后，验收入库时填写的收料单 B.增值税专用发票 C.机票  D.支付过桥费的收费收据 (单项选择题) A   B   C   D    答案是A. 本题分析  自制原始凭证在单位内部使用。";
    $row = "1. 下列各项，不属于专用记账凭证的是（）。 A收款B.凭证C.转账凭证 D.通用记账凭证  A B C D 答案是D. 本题分析  专用记账凭证包括：收款凭证、付款凭证、转账凭证。";
    echo 'row: '.$row.'<br>';
    $reg_option4split = '[A-F]\.|[0-9]\)|  *[A-F]|^[A-F]';
    //$pattern_match = '[0-9]+\..*?('.$reg_option4split.').*?('.$reg_option4split.').*?('.$reg_option4split.').*?('.$reg_option4split.').*?答案.*?本题分析.*?';
    //$pattern_replace = '([0-9]+\..*?)((?:'.$reg_option4split.').*?)((?:'.$reg_option4split.').*?)((?:'.$reg_option4split.').*?)((?:'.$reg_option4split.').*?)(答案.*?)(本题分析.*?)';
    //$replace = '$1\n$2\n$3\n$4\n$5\n$6\n$7';

    $exam_array = array();
for($opt_cnt = 5; $opt_cnt >= 0; $opt_cnt--){
    $pattern_match_pre = '[0-9]+\..*?';
    $pattern_match_post = '答案.*?本题分析.*?';
    $pattern_match_option='('.$reg_option4split.').*?';
    $pattern_replace_pre = '([0-9]+\..*?)';
    $pattern_replace_post = '(答案.*?)(本题分析.*?)';
    $pattern_replace_option = '((?:'.$reg_option4split.').*?)';
    $replace = '$1\n$2\n$3';

    $pattern_match = '';
    $pattern_replace = '';
    for($i=0; $i<$opt_cnt; $i++){
        $pattern_match = $pattern_match.$pattern_match_option;
        $pattern_replace = $pattern_replace.$pattern_replace_option;
        $replace = $replace.'\n$'.($i+4);
    }
    $pattern_match = $pattern_match_pre.$pattern_match.$pattern_match_post;
    $pattern_replace = $pattern_replace_pre.$pattern_replace.$pattern_replace_post;
    echo 'pattern_match: '.$pattern_match.'<br>';
    echo 'pattern_replace: '.$pattern_replace.'<br>';
    echo 'replace: '.$replace.'<br>';

    if(mb_preg_match_all('/'.$pattern_match.'/', $row, $matches)){
echo '<br>'.'match: '.$opt_cnt.'<br>';
        for($j = 0; $j < count($matches) - 1; $j++){
            $start = $matches[$j];
            $length = $matches[$j+1] - $start;
            array_push($exam_array, mb_substr($row, $start, $length));
        //    echo mb_substr($row, $start, $length).'<br>';
        }
        $start = $matches[$j];
        $length = mb_strlen($row) - $start;
        array_push($exam_array, mb_substr($row, $start, $length));

        print_r($exam_array);
        for($j = 0; $j < count($matches); $j++){
            $row = $exam_array[$j];
            $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
            echo '<br>';
            echo '<br>';
            print_r(explode('\n', $row));
        }
        break;
    }
    //else
    //{
    //    echo 'not match<br>';
    //}
}
/*
    $row = "1.【答案】D。解析:会计核算的基本方法包括设置会计科目和账户、复式记账、填制和审核会计凭证、登记账簿、成本计算、财产清查和编制财务报表七种方法，编制财务预算属于管理会计的范畴，不包括在会计核算的方法中。";
    $pattern = '^(.*答案.*)(解析:.*)';
    $replace = '$1\n$2';
    echo 'row: '.$row.'<br>';
    if(preg_match('/'.$pattern.'/', $row, $reg)){
        $row = preg_replace('/'.$pattern.'/', $replace, $row);
    }
    else
    {
        echo 'not match<br>';
    }
    print_r(explode('\n', $row));
*/
/*
    $row = "   1)生产职能  3)fd   2)反映B.监督职能";
    $cnt = 0;
    if(preg_match_all('/[0-9]\)/', $row, $reg)){
        $cnt = count($reg[0]);
        //$pattern = '^ *([0-9]\).*?) *([0-9]\).*?)';
        //$replace = '$1\n$2';
        $pattern = '^ *([0-9]\).*?)';
        $replace = '$1';
        for($i=1; $i<$cnt; $i++){
            $pattern = $pattern.' *([0-9]\).*?)';
            $replace = $replace.'\n$'.($i+1);
        }
        //echo $pattern.'<br>'.$replace.'<br>';
        echo 'row: '.$row.'<br>';
        $row = preg_replace('/'.$pattern.'/', $replace, $row);
        print_r(explode('\n', $row));
    }
*/
/*
    //$row = "A.收款凭证 B.付款凭证 C.转账凭证 D.通用记账凭证 D.通用记账凭证";
    //$row = "A.社会文明水平B.社会经济制度C.市场发达程度D.科技发展水平";
    //$row = "  A.社会文明水平B.社会经济制度 C.社会经济制度 ";
    $cnt = 0;
    if(preg_match_all('/[A-F]\./', $row, $reg)){
        $cnt = count($reg[0]);
        //$pattern = '^ *([A-F]\..*?) *([A-F]\..*?) *([A-F]\..*?) *([A-F]\..*?)';
        //$replace = '$1\n$2\n$3\n$4';
        $pattern = '^ *([A-F]\..*?)';
        $replace = '$1';
        for($i=1; $i<$cnt; $i++){
            $pattern = $pattern.' *([A-F]\..*?)';
            $replace = $replace.'\n$'.($i+1);
        }
        //echo $pattern.'<br>'.$replace.'<br>';
        echo 'row: '.$row.'<br>';
        $row = preg_replace('/'.$pattern.'/', $replace, $row);
        print_r(explode('\n', $row));
    }
*/
/*
    //blank before [A-F] is needed.
    $row = "A积累性支出 B转移性支出 C补偿性支出 D购买性支出";
    $cnt = 0;
    if(preg_match_all('/^[A-F]|  *[A-F]/', $row, $reg)){
        //echo '<br>'; print_r($reg);echo '<br>';
        $cnt = count($reg[0]);
        //$pattern = '^ *([A-F][^ ]*) *([A-F][^ ]*) *([A-F][^ ]*) *([A-F][^ ]*)';
        //$replace = '$1\n$2\n$3\n$4';
        $pattern = '^ *([A-F][^ ]*)';
        $replace = '$1';
        for($i=1; $i<$cnt; $i++){
            $pattern = $pattern.' *([A-F][^ ]*)';
            $replace = $replace.'\n$'.($i+1);
        }
        //echo $pattern.'<br>'.$replace.'<br>';
        //echo 'row: '.$row.'<br>';
        $row = preg_replace('/'.$pattern.'/', $replace, $row);
        print_r(explode('\n', $row));
    }
*/
/*
    array//" 1)生产职能 2)反映B.监督职能"
    (
        '^ *[0-9]\).*?[0-9]\).*',
        '^ *([0-9]\).*) *([0-9]\).*)',
        '$1\n$2',
    ),
    array//" A.社会文明水平B.社会经济制度.社会经济制度 "
    (
        '^ *[A-F]\.[^A-F]*[A-F]\.[^A-F]*$',    
        '^ *([A-F]\.[^A-F]*) *([A-F]\.[^A-F]*)',
        '$1\n$2',
    )
*/
}
$reg_question4role = '^ *[0-9]+\.|^ *第[0-9]+题';
$reg_option4role = '^ *[A-F]|^ *[0-9]\)';
$reg_answer4role = '^参考答案|^正确答案|^[0-9]+\.答案|^答案';
$reg_analysis4role = '^试题解析|^参考解析|^答案解析|^本题分析|^试题点评|^本题来源|^本题考点|^解析';

$reg_question4split = '[0-9]+ *\.|第 *[0-9]+ *题';
$reg_option4split = '[A-F]\.|[0-9]\)|  *[A-F]|^[A-F]';
$reg_answer4split = '参考答案|正确答案|[0-9]+\.答案|答案';
$reg_analysis4split = '试题解析|参考解析|答案解析|本题分析|试题点评|本题来源|本题考点|解析';

function echo_error($error, $func){
    echo '<font color="red">Error in function'.$func.': '.$error.'</font><br>';
}
//split_option();
function split_question(){
    //dot after [A-F] is needed.
    $row = "1. 下列各项，不属于专用记账凭证的是（）。 A收款凭证 2)付款凭证 C.转账凭证 D.通用记账凭证 (单项选择题) A   B   C   D    答案是D. 本题分析  专用记账凭证包括：收款凭证、付款凭证、转账凭证。 2.  下列原始凭证属于自制原始凭证的是（）。 A.入材料后，验收入库时填写的收料单 B.增值税专用发票 C.机票  D.支付过桥费的收费收据 (单项选择题) A   B   C   D    答案是A. 本题分析  自制原始凭证在单位内部使用。";
    //$row = "1. 下列各项，不属于专用记账凭证的是（）。 A收款B.凭证C.转账凭证 D.通用记账凭证 (单项选择题) A   B   C   D 答案是D. 本题分析  专用记账凭证包括：收款凭证、付款凭证、转账凭证。";
    $row = preg_replace('/(  *[A-F] *?){2,}/', '', $row);
    $row = preg_replace('/(单项选择题)/', '', $row);
    echo 'row: '.$row.'<br>';
    //$pattern_match = '[0-9]+\..*?('.$reg_option4split.').*?('.$reg_option4split.').*?('.$reg_option4split.').*?('.$reg_option4split.').*?答案.*?本题分析.*?';
    //$pattern_replace = '([0-9]+\..*?)((?:'.$reg_option4split.').*?)((?:'.$reg_option4split.').*?)((?:'.$reg_option4split.').*?)((?:'.$reg_option4split.').*?)(答案.*?)(本题分析.*?)';
    //$replace = '$1\n$2\n$3\n$4\n$5\n$6\n$7';
    global $reg_question4split;
    global $reg_option4split;
    $exam_array = array();
    if(mb_preg_match_all('/'.$reg_question4split.'/', $row, $matches)){
        $question_cnt = count($matches);
        for($j = 0; $j < $question_cnt - 1; $j++){
            $start = $matches[$j];
            $length = $matches[$j+1] - $start;
            array_push($exam_array, mb_substr($row, $start, $length));
        }
        $start = $matches[$j];
        $length = mb_strlen($row) - $start;
        array_push($exam_array, mb_substr($row, $start, $length));
    }
    echo '<font color="red">exam_array: </font><br>';
    print_r($exam_array);

    for($i=0; $i < count($exam_array); $i++){
        $row = $exam_array[$i];
        for($j = 5; $j >= 0; $j--){
            $pattern_match_pre = '[0-9]+\..*?';
            $pattern_match_post = '答案.*?本题分析.*?';
            $pattern_match_option='('.$reg_option4split.').*?';
            $pattern_replace_pre = '([0-9]+\..*?)';
            $pattern_replace_post = '(答案.*?)(本题分析.*?)';
            $pattern_replace_option = '(?:'.$reg_option4split.').*?';
            $replace = '$1\n$2\n$3\n$4';
            $pattern_match = $pattern_match_pre.'('.$pattern_match_option.'){'.$j.'}'.$pattern_match_post;
            $pattern_replace = $pattern_replace_pre.'((?:'.$pattern_replace_option.'){'.$j.'})'.$pattern_replace_post;
            if(mb_preg_match_all('/'.$pattern_match.'/', $row, $matches)){
                $opt_cnt = $j;
                echo '<br>option count: '.$opt_cnt.'<br>';
                echo 'pattern match: '.$pattern_match.'<br>';
                echo 'pattern replace: '.$pattern_replace.'<br>';
                break;
            }
        }
        if($opt_cnt > 1)
        {
            $row = preg_replace('/'.$pattern_replace.'/', $replace, $row);
            print_r(explode('\n', $row));
        }elseif($opt_cnt == 1){
        }else{
        }
    }
}
//split_question();
function pre_treat_row(){
    //$row = preg_replace('/(  *[A-F] *?){2,}/', '', $row);
    //$row = preg_replace('/(单项选择题)/', '', $row);
$row = '2. 【答案】A。解析：填制会计凭证是会计核算工作的起点。';
    echo 'before: '.$row.'<br>';
    $row = preg_replace('/ *【(.*)】/', '$1', $row);
    echo 'after : '.$row.'<br>';
}
//pre_treat_row();
?> 
