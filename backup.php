<?php

/*
	    //if row begin with [A-F] or [0-9]+, dot will be followed.
        if(preg_match('/^ *[A-F]/', $row)){
		    if(!preg_match('/^ *[A-F]\./', $row)){
                $row = preg_replace('/^( *[A-Z])/', '${1}.', $row);
		    }
        }
        //begin with number, no dot followed, and not ) followed.
        if(preg_match('/^ *[0-9]+/', $row)){
            if(!preg_match('/^ *[0-9]+\./', $row) && !preg_match('/^ *[0-9]+\)/', $row)){            
                $row = preg_replace('/^( *[0-9]+)/', '${1}.', $row); 
            }
        }
*/		

function get_answer(&$question, $row, &$cnt){
    mb_preg_match_all('/[A-F]/', $row, $matches);
    for($i=0; $i<count($matches); $i++){
        $question['answer'][$i] = mb_substr($row, $matches[$i], 1);
    }
    $cnt = $i;
}
function get_role_array($arr, $role){
	/*
	assume: 1.question;2.options;3.analysis or answer
	*/
    $question = null;
	$option_cnt=0;
	$answer_cnt=0;
	$question_array  = array();
    for($i=0; $i < count($role); $i++){
	    switch($role[$i])
		{
		case 'question':
		    if($question != null){
			    if($answer_cnt == 0){
			        get_answer($question, $question['question'], $answer_cnt);
			    }
		        array_push($question_array, $question);
	            $option_cnt=0;
	            $answer_cnt=0;
		    }
		    $question = array();
		    $question['question'] = $arr[$i];
	        $option_cnt=0;
	        $answer_cnt=0;
		break;
		case 'option':
		    $question['option'][$option_cnt++] = $arr[$i];
		break;
		case 'analysis':
		    $question['analysis'] = $arr[$i];
		break;
		case 'answer':
	        get_answer($question, $arr[$i], $answer_cnt);
		break;
		case 'unknown':
		break;
		default:
		break;
		}
    }
	if($question != null){
		if($answer_cnt == 0){
		    get_answer($question, $question['question'], $answer_cnt);
		}
	    array_push($question_array, $question);
	    $option_cnt=0;
	    $answer_cnt=0;
	}
    return $question_array;
}

function need_split_question($reg, $row){
    global $reg_question4split;
    global $reg_option4split;
    if(preg_match_all($reg, $row, $regs)){
        $str_find = $regs[0];
        if(count($str_find) > 1){
            return true;
        }else{
            return false;
        }
    }
}
//row like "1.*2.*" will be splited. 
function split_question($row, &$arr_new){
    global $reg_question4split;
    global $reg_option4split;
    global $reg_answer4split;
 //   $arr_new = array();
 //   for($i=0; $i < count($arr); $i++){
 //       $row = $arr[$i];
    $reg_all = '/'.$reg_question4split.'|'.$reg_option4split.'|'.$reg_answer4split .'/';
        if(!need_split_question($reg_all, $row)){
            array_push($arr_new, $row);
            return;
        }
        if(mb_preg_match_all($reg_all, $row, $matches)){
            for($j = 0; $j < count($matches) - 1; $j++){
                $start = $matches[$j];
                $length = $matches[$j+1] - $start;
                array_push($arr_new, mb_substr($row, $start, $length));
            //    echo mb_substr($row, $start, $length).'<br>';
            }
            $start = $matches[$j];
            $length = mb_strlen($row) - $start;
            array_push($arr_new, mb_substr($row, $start, $length));
            //    echo mb_substr($row, $start, $length).'<br>';
        }
//   }
//    return $arr_new;
}
function need_split_option($row){
    global $reg_question4split;
    global $reg_option4split;
    if(preg_match_all('/'.$reg_option4split.'/', $row, $regs)){
        $str_find = $regs[0];
        if(count($str_find) > 1){
            return true;
        }else{
            return false;
        }
    }
}
function split_option($row, &$arr_new){
    global $reg_question4split;
    global $reg_option4split;
    if(!need_split_option($row)){
        array_push($arr_new, $row);
        return;
    }
    if(mb_preg_match_all('/'.$reg_option4split.'/', $row, $matches)){
        for($j = 0; $j < count($matches) - 1; $j++){
            $start = $matches[$j];
            $length = $matches[$j+1] - $start;
            array_push($arr_new, mb_substr($row, $start, $length));
        //    echo mb_substr($row, $start, $length).'<br>';
        }
        $start = $matches[$j];
        $length = mb_strlen($row) - $start;
        array_push($arr_new, mb_substr($row, $start, $length));
        //    echo mb_substr($row, $start, $length).'<br>';
    }
}
function split_content($arr){
    global $reg_question4role;
    global $reg_option4role;
    $arr_new = array();
    for($i=0; $i < count($arr); $i++){
        $row = $arr[$i];
        if(preg_match('/'.$reg_question4role.'/', $row, $regs)){
            split_question($row, $arr_new);
        }elseif(preg_match('/'.$reg_option4role.'/', $row, $regs)){
            split_option($row, $arr_new);
        }else{
            array_push($arr_new, $row);
        }
    }
    return $arr_new;
}
?>

<?php
function convSymbol($s) {
    $ary = array(
        "\r"=>"",
        "\n"=>"",
        "　"=>"",
        "  "=>" ",
        "："=>":",
        "．"=>".",
        "、"=>".",
        "（"=>"(",
        "）"=>")",
        "ａ"=>"a",
        "ｂ"=>"b",
        "ｃ"=>"c",
        "ｄ"=>"d",
        "ｅ"=>"e",
        "ｆ"=>"f",
        "Ａ"=>"a",
        "Ｂ"=>"b",
        "Ｃ"=>"c",
        "Ｄ"=>"d",
        "Ｅ"=>"e",
        "Ｆ"=>"f",
    );
    reset($ary);
    while(list($key, $val) = each($ary)) {
        do {
            $r = strpos($s, $key);
            if ($r === false) {
            	break;
            }
            $s = substr($s, 0, $r).$val.substr($s, $r+strlen($key));
        } while (true);
    }
//    echo "<br>result: ".trim($s)."<br>";
    return trim($s);
}

function parse_item_index(&$s) {
	$i = strpos($s, ".", 0);
	if ($i === false) {
		return false;
	}
	$t = substr($s, 0, $i);
	$idx = intval($t);
	if ($idx <= 0) {
		return false;
	}
	$s = substr($s, $i+1);
	return $idx;
}
function parse_item_answ_from_cont(&$cont) {
	$s = $cont;
	$pos = 0;
	do {
		$i = strpos($s, "(", $pos);
		if ($i === false) {
			break;
		}
		$j = strpos($s, ")", $i+1);
		if ($j === false) {
			break;
		}
		$t = substr($s, $i+1, $j-$i-1);
		$rst = parse_item_answ_multi($t);
		if ($rst !== false) {
			$cont = substr($s, 0, $i+1).substr($s, $j);
			return $rst;
		}
		$pos = $j+1;
	} while (true);

	$t = $s[strlen($s)-1];
	$t = strtoupper($t);
	if ((($t >= '0') && ($t <= '9')) || (($t >= 'A') && ($t <= 'Z'))) {
		$cont = substr($s, 0, strlen($s)-1);
		return parse_item_answ($t);
	}

	return false;
}
function parse_item_answ($t) {
	$t = strtoupper($t);
	if (($t >= '0' && $t <= '9') || ($t >= 'A' && $t <= 'Z')) {
		if ($t >= 'A' && $t <= 'Z') {
			$t = chr(ord('1') + (ord($t) - ord('A')));
		}
		return $t;
	}
	return false;
}
function parse_item_answ_multi($t) {
	$t = strtoupper(trim($t));
	$is_result = (strlen($t) > 0) ? true : false;
	for ($x=0; $x<strlen($t); $x++) {
		if ((($t[$x] >= '0') && ($t[$x] <= '9')) || (($t[$x] >= 'A') && ($t[$x] <= 'Z'))) {
			continue;
		} else {
			$is_result = false;
			break;
		}
	}
	if ($is_result) {
		if (strlen($t) <= 1) {
			return parse_item_answ($t);
		} else {
			$ary = array();
			for ($x=0; $x<strlen($t); $x++) {
				$ary[$x] = parse_item_answ($t[$x]);
			}
			return $ary;
		}
	}
	return false;
}
function parse_item_optn(&$s) {
	$got = true;
	$i = strpos($s, ".");				// A.xxx
	if (($i === false) || ($i > 1)) {
		$i = strpos($s, ":");			// A:xxx
		if (($i === false) || ($i > 1)) {
			$i = strpos($s, " ");		// A xxx
			if (($i === false) || ($i > 1)) {
				$i = strpos($s, ")");	// A)
				if (($i === false) || ($i > 1)) {
					$got = false;
				}
			}
		}
	}
	if (!$got) {
		$i = 1;
	}
	if (1 != $i) {
		return false;
	}
	$t = substr($s, 0, $i);
	$t = strtoupper($t);
	$i = $got ? $i+1 : $i;
	if (($t >= '0' && $t <= '9') || ($t >= 'A' && $t <= 'Z')) {
		$t = parse_item_answ($t);
		$s = trim(substr($s, $i));
		$i = strpos($s, " ");
		$ary = array();
		if ($i !== false) {
			$x = trim(substr($s, $i+1));
			$tmp = parse_item_optn($x);
			if ($tmp !== false) {
				$ary[$t] = substr($s, 0, $i);
				while (list($key, $val) = each($tmp)) {
					$ary[$key] = $val;
				}
			} else {
				$ary[$t] = $s;
			}
		} else {
			$ary[$t] = $s;
		}
		$s = "";
		return $ary;
	} else {
		return false;
	}
}
function parse_item($fp, &$more, &$answ_ahead) {
	$item = array();
	$state = 0;
	$lines = null;
	$linesorg = null;
	$last_more = null;
	$answ_ahead = null;
	do {
		if ($lines != null) {
			$tmp = $lines;
			$lines = null;
			$more = $tmp[0];
			for ($i=1; $i<count($tmp); $i++) {
				$lines[$i-1] = $tmp[$i];
			}
		}
		if (null != $more) {
			$s = $more;
			$more = null;
			$last_more = $s;
		} else {
			$s = fgets($fp, 4096);
		}
//echo "===>$state: $s\r\n<br/>";
		$need_goto = false;
		if ($s === false) {
			if ($state >= 1) {
				$state = 2;
				$need_goto = true;
			} else {
				break;
			}
		}
		if (!$need_goto) {
			$x = strpos($s, "。");
			$y = strpos($s, "答案", ($x===false)?0:$x+2);
			if (($x !== false) && ($y !== false) && ($x < $y)) {
				$lines[0] = substr($s, $x+2, $y-$x-2);
				$lines[1] = substr($s, $y);
				$linesorg = $s;
				$s = substr($s, 0, $x+2);
			} else if ($y !== false) {
				$x = strpos($s, ".");
				if (($x !== false) && ($x < $y)) {
					$lines[0] = substr($s, $y);
					$linesorg = $s;
					$s = substr($s, 0, $y);
				}
			}
			$s = trim($s);
			if (strlen($s) <= 0) {
				if ($state == 1) {
					++$state;
				}
				continue;
			}
			$s = convSymbol($s);
		}
//final_end:
		switch ($state) {
			case 0:	// 找开头
				$idx = parse_item_index($s);
				if ($idx === false) {
					break;
				}
				$item["cont"] = $s;
				$state++;
				break;
			case 1:	// 找选项
				$tmp = parse_item_optn($s);
//print_r($tmp);
				if ($tmp === false) {
					$state++;
					$more = $s;
				} else {
					if (!array_key_exists("optn", $item)) {
						$item["optn"] = $tmp;
					} else {
						while (list($key, $val) = each($tmp)) {
							$item["optn"][$key] = $val;
						}
					}
				}
				break;
			case 2:	// 找答案和解析，并找下一个开头
				$i = strpos($s, ":");
				if ($i !== false) {
					$t = substr($s, 0, $i);
					if (("答案" == $t) || ("【标准答案】" == $t)|| ("【答案】" == $t)|| ("【正确答案】" == $t)|| ("【参考答案】" == $t)|| ("参考答案" == $t)) {
						$answ = strtoupper(trim(substr($s, $i+1)));
						if (strlen($answ) <= 0) {
							$s = fgets($fp);
							$s = convSymbol(trim($s));
							$answ = $s[strlen($s)-1];
							$answ_ahead = substr($s, 0, strlen($s)-1);
						}
						$item["answ"] = parse_item_answ_multi($answ);
						if ($item["answ"] === false) {
							$item["answ"] = $answ;
						}
					} else if (("解析" == $t) || ("【试题解析】" == $t)|| ("【解析】" == $t)|| ("【答案解析】" == $t)) {
						$item["hint"] = substr($s, $i+1);
					} else {
						$i = false;
					}
				}
				if ($i === false) {
					$t = $s;
					$idx = parse_item_index($s);
					if (($idx !== false) || ($s === false)) {
						$more = (null == $lines) ? $t : $linesorg;
						if (!array_key_exists("answ", $item)) {
							$item["answ"] = parse_item_answ_from_cont($item["cont"]);
						}
						return $item;
					} else if ($last_more != $s) {
						$more = $s;
						$state = 1;
					}
				}
				break;
		}
	} while (true);

	return false;
}
/*******************************************************************************
 * $result(
 *  $item(
 *   "cont"	=>题目内容
 *   "optn"	=>$options(
 *    选项内容A,
 *    选项内容B,
 *    选项内容C,
 *    ...
 *   ) or null
 *   "answ"	=>答案(A/B/C/D/..., Y/N)
 *   "hint"	=>解析
 *  )
 * )
 ******************************************************************************/
function parse($path_or_str) {
	if (file_exists($path_or_str)) {
		$fp = fopen($path_or_str, "rb");
		if ($fp === false) {
			return false;
		}
	} else {
		$fp = tmpfile();
		fwrite($fp, $path_or_str);
		fseek($fp, 0);
	}

	$result = array();
	$more = null;
	do {
		$item = parse_item($fp, $more, $answ);
		if ($item === false) {
			break;
		}
		$result[count($result)] = $item;
		if ((null != $answ) && (strlen($answ) > 0)) {
//echo "--->(1)$answ\r\n";
			if (strpos($answ, ".") > 0) {
				$answ = str_replace(".", "", $answ);
				$tmp = "";
				for ($x=0; $x<strlen($answ); $x++) {
					if (0 == ($x%2)) {
						continue;
					}
					$tmp = $tmp.$answ[$x];
				}
				$answ = $tmp;
			}
//echo "--->(2)$answ\r\n";
			$n = strlen($answ);
			$i = count($result)-1-$n;
			if ($i < 0) {
				$n = $n + $i;
				$i = 0;
			}
			for ($j=0; $n>0; $i++, $j++, $n--) {
				if (array_key_exists("answ", $result[$i]) && ($result[$i]["answ"] != null)) {
					continue;
				}
				$result[$i]["answ"] = parse_item_answ($answ[$j]);
				if ($result[$i]["answ"] === false) {
					$result[$i]["answ"] = $answ[$j];
				}
			}
		}
	} while (true);
	fclose($fp);

	return $result;
}

function split_answer_role4test(&$arr, &$arr_role){
    //$array_queue = array();
    //$exam_array = array();
    //$cur_exam = array();
    $content_array = array();
    $role_array = array();
    $search = array();
    $replace = array();
    array_push($search, '\( *([A-F]) *\)');
    array_push($replace, '()');
    array_push($search, '([A-F])$');
    array_push($replace, '');
    for($i = 0; $i < count($arr); $i++){
        $row_tmp =  $arr[$i];
        $role_tmp = $arr_role[$i];
        if($role_tmp == "question"){
            $find_answer = false;
            for($j = 0; $j < count($search); $j++){
                if(preg_match('/'.$search[$j].'/', $row_tmp, $reg)){
                    array_push($content_array, preg_replace('/'.$search[$j].'/', $replace[$j], $row_tmp));
                    array_push($role_array, $role_tmp);
                    $content2add = '答案'.$reg[count($reg) - 1];
                    array_push($content_array, $content2add);
                    array_push($role_array, get_role_by_row($content2add));
                    $find_answer = true;
                    break ;
                    //print_r($reg);
                    //echo '<br>'.preg_replace('/'.$search[$j].'/', $replace[$j], $row).'<br>';
                }
            }
            if(!$find_answer){
                array_push($content_array, $arr[$i]);
                array_push($role_array, $role_tmp);
            }
            continue;
        }
        else
	if($role_tmp == "answer"){
            //if(preg_match('/[0-9]+-[0-9]+[A-F]+/', $row_tmp)){
                mb_preg_match_all('/[A-F]/', $row_tmp, $matches);
                for($k=0; $k<count($matches); $k++){
                    array_push($content_array, mb_substr($row_tmp, $matches[$k], 1));
                    array_push($role_array, $role_tmp);
                }
                continue;
            //}
	}
        array_push($content_array, $row_tmp);
        array_push($role_array, $role_tmp);
    }
    $arr = $content_array;
    $arr_role = $role_array;
}
function canpush2array($arr){
//print_r($arr);
//echo '<br>';
    if(array_key_exists("answer", $arr) && array_key_exists("question", $arr)){
        return true;
    }else{
        return false;
    }
}
function split_answer_role(&$arr, &$arr_role){
    $array_queue = array();
    $exam_array = array();
    $cur_exam = array();
    $search = array();
    $replace = array();
    array_push($search, '\( *([A-F]) *\)');
    array_push($replace, '()');
    array_push($search, '([A-F])$');
    array_push($replace, '');
    for($i = 0; $i < count($arr); $i++){
        $row_tmp =  $arr[$i];
        $role_tmp = $arr_role[$i];
		//echo $row_tmp.$role_tmp.'<br>';
        if($role_tmp == "question"){
            if(count($cur_exam)>0){
                if(canpush2array($cur_exam)){
                    array_push($exam_array, $cur_exam);
                }
                else{
                    array_push($array_queue, $cur_exam);
                }
            }
            $cur_exam = array();
            $cur_exam['option'] = array();
            $find_answer = false;
            for($j = 0; $j < count($search); $j++){
                if(preg_match('/'.$search[$j].'/', $row_tmp, $reg)){
                    $cur_exam['question'] = preg_replace('/'.$search[$j].'/', $replace[$j], $row_tmp);
                    $cur_exam['answer'] = $reg[count($reg) - 1];
                    $find_answer = true;
                    break ;
                    //print_r($reg);
                    //echo '<br>'.preg_replace('/'.$search[$j].'/', $replace[$j], $row).'<br>';
                }
            }
            if(!$find_answer){
                $cur_exam['question'] = $row_tmp;
            }
            continue;
        }
        else
        if($role_tmp == "analysis"){
            $cur_exam['analysis'] = $row_tmp;
        }
        else
        if($role_tmp == "option"){
            array_push($cur_exam['option'], $row_tmp);
        }
        else
        if($role_tmp == "answer"){
            //if(preg_match('/[0-9]+-[0-9]+[A-F]+/', $row_tmp)){
                mb_preg_match_all('/[A-F]/', $row_tmp, $matches);
                while(count($array_queue) + 1 > count($matches)){
                    array_shift($array_queue);
                }
                for($k=0; $k<count($matches)-1; $k++){
                    $match_tmp = mb_substr($row_tmp, $matches[$k], 1);
                    $exam_tmp = array_shift($array_queue);
                    $exam_tmp['answer'] = $match_tmp;
                    if(canpush2array($exam_tmp)){
                        array_push($exam_array, $exam_tmp);
                    }
                }
				$match_tmp = mb_substr($row_tmp, $matches[$k], 1);
				$cur_exam['answer'] = $match_tmp;
            //}
	    }
    }	
    if(canpush2array($cur_exam)){
        array_push($exam_array, $cur_exam);
    }
    return $exam_array;
}


function check_role(&$arr, &$role_arr){
    global $global_role2index;
    $content_array = array();
    $role_array = array();
    $i = 0;
    $j = 0;
    $role = $role_arr[$i];
    while(($role != 'question') && ($i<count($role_arr))){
        $role = $role_arr[++$i];
    }
    $content_array[$j] = $arr[$i];
    $role_array[$j] = $role_arr[$i];
    $i++;
    $j++;
    for(;$i<count($role_arr); $i++){
        $pre_role = $role_arr[$i-1];
        $role = $role_arr[$i];
    	//adjcent element hava the same role will be merged. 
    	//the unknown element will merge with previous sibling. 
    	if(($role == $pre_role && $role!='option') || ($role == 'unknown')){
    	    $content_array[$j-1] = $content_array[$j-1].$arr[$i];
    	    continue;
    	}
    	if(($role != 'question') && ($global_role2index[$role] < $global_role2index[$pre_role])){
    	    if($role == 'option'){
    	        $content_array[$j-1] = $content_array[$j-1].$arr[$i];
    	            continue;
            }
        }
        $content_array[$j] = $arr[$i];
        $role_array[$j] = $role_arr[$i];
        $j++;
    }
    //print_r($content_array);
    //echo '<br>';
    //print_r($role_array);
    //echo '<br>';
    $arr = $content_array;
    $role_arr = $role_array;
    
    

    //in function get_and_check_roles(&$arr)
    /*
     global $global_role2index;
    $content_array = array();
    $role_array= array();
    $i = 0;
    $j = 0;
    $content_array[$j] = $content_pre[$i];
    $role_array[$j] = $role_pre[$i];
    $i++;
    $j++;
    for(;$i<count($role_pre); $i++){
    $need_fix = false;
    $pre_role = $role_pre[$i-1];
    $cur_role = $role_pre[$i];
    if($i+1 < count($role_pre)){
    $next_role =  $role_pre[$i+1];
    }
    
    //echo 'i-1:'.$content_pre[$i-1].'<br>';
    //echo 'i:'.$content_pre[$i].'<br>';
    //echo 'i+1:'.$content_pre[$i+1].'<br>';
    //echo 'pre role:'.$pre_role.'<br>';
    //echo 'cur role:'.$cur_role.'<br>';
    //echo 'next role:'.$next_role.'<br>';
    //echo 'content:'.$content_tmp.'<br>';
    //echo 'role_tmp:'.$role_tmp.'<br>';
    //echo '<br>';
    
    if($pre_role == "unknown"){
    //答案：
    //ab
    if($cur_role == "unknown"){
    $content_tmp = $content_pre[$i-1].$content_pre[$i];
    $role_tmp = get_role_by_row($content_tmp);
    if($role_tmp !=  "unknown"){
    $content_array[$j-1] = $content_tmp;
    $role_array[$j-1] = $role_tmp;
    $need_fix = true;
    }
    }
    
    else
    	//答案：
    //ad
    if($cur_role == "option"){
    $content_tmp = $content_pre[$i-1].$content_pre[$i];
    $role_tmp = get_role_by_row($content_tmp);
    if($role_tmp !=  "unknown"){
    $content_array[$j-1] = $content_tmp;
    $role_array[$j-1] = $role_tmp;
    $need_fix = true;
    }
    }
    //else
    	//答案：
    //1.a2.d
    //if($cur_role == "question"){
    //    $content_tmp = $content_pre[$i-1].$content_pre[$i];
    //	echo 'content tmp:'.$content_tmp.'<br>';
    //    $role_tmp = get_role_by_row($content_tmp);
    //    if($role_tmp !=  "unknown"){
    //        $content_array[$j-1] = $content_tmp;
    //        $role_array[$j-1] = $role_tmp;
    //        $need_fix = true;
    //    }
    //}
    
    //else
    	//1、
    //【正确答案】：B
    //if($cur_role == "answer"){
    //    $content_tmp = $content_pre[$i-1].$content_pre[$i];
    //    $role_tmp = get_role_by_row($content_tmp);
    //    if($role_tmp !=  "unknown"){
    //        $content_array[$j-1] = $content_tmp;
    //        $role_array[$j-1] = $role_tmp;
    //        $need_fix = true;
    //    }
    //}
    
    }else
    if($pre_role == "analysis"){
    
    //本题来源2013年经济师《初级经济基础》真题第34题
    //本题考点诉讼与仲裁法律基础知识>民事诉讼法基础知识>公益诉讼的含义
    //if($cur_role == "analysis"){
    //    $content_tmp = $content_pre[$i-1].$content_pre[$i];
    //    $role_tmp = get_role_by_row($content_tmp);
    //    if($role_tmp !=  "unknown"){
    //        $content_array[$j-1] = $content_tmp;
    //        $role_array[$j-1] = $role_tmp;
    //        $need_fix = true;
    //    }
    //}else
    
    	//参考解析.
    //各类经济合同.以合同上所记载的金额.(unknown)
    if($cur_role == "unknown"){
    $content_tmp = $content_pre[$i-1].$content_pre[$i];
    $role_tmp = get_role_by_row($content_tmp);
    if($role_tmp !=  "unknown"){
    $content_array[$j-1] = $content_tmp;
    $role_array[$j-1] = $role_tmp;
    $need_fix = true;
    }
    }
    }
    
    if(!$need_fix){
    if($cur_role == "unknown")
    {
    continue;
    }
    $content_array[$j] = $content_pre[$i];
    $role_array[$j] = $role_pre[$i];
    $j++;
    }
    }
    */
    //$arr = $content_array;
    //return $role_array;
    
/*

            case $separate_answer_analysis_from_question:
                //122221222212222343434
                //12{4}){2,}(?:34){2,}'
                if(preg_match_all('/12{4}/', $role_line_tmp, $reg_q_o, PREG_OFFSET_CAPTURE) 
                && preg_match_all('/34/', $role_line_tmp, $reg_a_a, PREG_OFFSET_CAPTURE)){
                    if(count($reg_q_o[0]) != count($reg_a_a[0])){
                        echo_error('count of question and option is different from answer and analysis.', 'split_rows2array');
                        continue;
                    }
                }else
                {
                    echo_error('count of question and option is different from answer and analysis.', 'split_rows2array');
                    continue;
                }
                for($k = 0; $k < count($reg_q_o[0]); $k++){
                    $exam_cur = array();
                    $exam_cur['type'] = $reg_type;
                    $exam_cur['option'] = array();
                    $exam_cur['analysis'] = array();
                	//$exam_cur['answer'] = '';
                    $line_tmp = '12222';
                    $start_pos_tmp =  $reg_q_o[0][$k][1];
                    for($m=0; $m<strlen($line_tmp); $m++){
                        $role_tmp = $global_index2role[$line_tmp[$m]];
                        //echo 'role_tmp'.$role_tmp.'<br>';
                        if($role_tmp == 'option'){
                            array_push($exam_cur[$role_tmp], $arr[$start_pos+$start_pos_tmp+$m]);
                        }
                        elseif($role_tmp == 'analysis'){
                            $exam_cur['analysis'] = array_merge($exam_cur['analysis'], explode('\n', $arr[$start_pos+$start_pos_tmp+$m]));
                        }
                        else{
                            $exam_cur[$role_tmp] = $arr[$start_pos+$start_pos_tmp+$m];
                        }
                    }
                    $line_tmp = '34';
                    $start_pos_tmp =  $reg_a_a[0][$k][1];
                    for($m=0; $m<strlen($line_tmp); $m++){
                        $role_tmp = $global_index2role[$line_tmp[$m]];
                        //echo 'role_tmp'.$role_tmp.'<br>';
                        if($role_tmp == 'option'){
                            array_push($exam_cur[$role_tmp], $arr[$start_pos+$start_pos_tmp+$m]);
                        }
                        elseif($role_tmp == 'analysis'){
                            $exam_cur['analysis'] = array_merge($exam_cur['analysis'], explode('\n', $arr[$start_pos+$start_pos_tmp+$m]));
                        }                        
                        else{
                            $exam_cur[$role_tmp] = $arr[$start_pos+$start_pos_tmp+$m];
                        }
                    }
                    array_push($exam_array, post_treat_row($exam_cur, $reg_type));
                }
                //echo '<br>split type 4<br>';
                //print_r($reg_q_o[0]);
                //echo '<br>';
                //print_r($reg_a_a[0]);
                //echo '<br>';            
            break;
            case $separate_answer_from_question:
                //'(?:12{4}){2,}3{2,}';
                if(preg_match_all('/12{4}/', $role_line_tmp, $reg_q_o, PREG_OFFSET_CAPTURE) 
                && preg_match_all('/3/', $role_line_tmp, $reg_a_a, PREG_OFFSET_CAPTURE)){
                    if(count($reg_q_o[0]) != count($reg_a_a[0])){
                        echo_error('count of question and option is different from answer and analysis.', 'split_rows2array');
                        continue;
                    }
                }else
                {
                    echo_error('count of question and option is different from answer and analysis.', 'split_rows2array');
                    continue;
                }
                for($k = 0; $k < count($reg_q_o[0]); $k++){
                    $exam_cur = array();
                    $exam_cur['type'] = $reg_type;
                    $exam_cur['option'] = array();
                    $exam_cur['analysis'] = array();
                    $line_tmp = '12222';
                    $start_pos_tmp =  $reg_q_o[0][$k][1];
                    for($m=0; $m<strlen($line_tmp); $m++){
                        $role_tmp = $global_index2role[$line_tmp[$m]];
                        //echo 'role_tmp'.$role_tmp.'<br>';
                        if($role_tmp == 'option'){
                            array_push($exam_cur[$role_tmp], $arr[$start_pos+$start_pos_tmp+$m]);
                        }
                        elseif($role_tmp == 'analysis'){
                            $exam_cur['analysis'] = array_merge($exam_cur['analysis'], explode('\n', $arr[$start_pos+$start_pos_tmp+$m]));
                        }
                        else{
                            $exam_cur[$role_tmp] = $arr[$start_pos+$start_pos_tmp+$m];
                        }
                    }
                    $line_tmp = '3';
                    $start_pos_tmp =  $reg_a_a[0][$k][1];
                    for($m=0; $m<strlen($line_tmp); $m++){
                        $role_tmp = $global_index2role[$line_tmp[$m]];
                        //echo 'role_tmp'.$role_tmp.'<br>';
                        if($role_tmp == 'option'){
                            array_push($exam_cur[$role_tmp], $arr[$start_pos+$start_pos_tmp+$m]);
                        }
                        elseif($role_tmp == 'analysis'){
                            $exam_cur['analysis'] = array_merge($exam_cur['analysis'], explode('\n', $arr[$start_pos+$start_pos_tmp+$m]));
                        }
                        else{
                            $exam_cur[$role_tmp] = $arr[$start_pos+$start_pos_tmp+$m];
                        }
                    }//
                    array_push($exam_array, post_treat_row($exam_cur, $reg_type));
                }
            break;
 */
in function find_split_answer(){
/*
 //case2:decide wether answer need split.
//if answer follow by questionoption{0.5}
$j = $i;
while(($j>=0) && ($role_line_pre[$j]!='1')){
$j--;
}
//echo '$role_line_pre:'.$role_line_pre.'<br>';
//echo 'sub string['.$j.'-'.$i.']:'.mb_substr($role_line_pre, $j, $i-$j);
if(preg_match('/^12{0,5}$/', mb_substr($role_line_pre, $j, $i-$j))){
//if(preg_match('/[0-9]+-[0-9]+[A-F]+/', $row_tmp)){
if(mb_preg_match_all('/[A-FT]/', $row_tmp, $matches)){
//echo '<br>row_tmp:'.$row_tmp;
//echo '<br>matches:';print_r($matches);
for($k=0; $k<count($matches); $k++){
array_push($content_pre, mb_substr($row_tmp, $matches[$k], 1));
//array_push($content_pre, $row_tmp[$matches[$k]]);
array_push($role_pre, $role_tmp);
}
}
}
*/
/*
    if(mb_preg_match_all('/[A-FT]/', $row_tmp, $matches)){//√×
        //echo '<br>row_tmp:'.$row_tmp;
        //echo '<br>matches:';print_r($matches);echo '<br>';
        $str_tmp = '';                    
        for($k=0; $k<count($matches); $k++){
            //echo 'substr'.mb_substr($row_tmp, $matches[$k], 1).'<br>';
            $str_tmp = $str_tmp.mb_substr($row_tmp, $matches[$k], 1);
        }
        array_push($content_pre, $str_tmp);
        array_push($role_pre, $role_tmp);
    } 
*/

    
}
?>
