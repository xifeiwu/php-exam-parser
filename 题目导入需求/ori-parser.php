<?php
function convSymbol($s) {
	$ary = array(
//					" "=>"",
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
	while (list($key, $val) = each($ary)) {
		do {
			$r = strpos($s, $key);
			if ($r === false) {
				break;
			}
			$s = substr($s, 0, $r).$val.substr($s, $r+strlen($key));
		} while (true);
	}
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
//*
//if ($argc < 2) {
//	echo "usage: php $argv[0] <file>";
//	die;
//}
//$s = file_get_contents($argv[1]);

$s=$_POST['s'];

print_r(parse($s));
?>
<form method="post" action="test.php">
	<textarea name="s" rows=20 cols=150>
		</textarea>
		<input type="submit">
	</form>