<?php 
mb_internal_encoding('UTF-8');
//like preg_match_all, but can be used for chinese character.
function mb_preg_match_all($pattern, $subject, &$matches){
    //    echo $pattern.'<br>';
    //    echo $subject.'<br>';
    if(preg_match_all($pattern, $subject, $regs)){
        $str_find = $regs[0];
        $start = 0;
        $matches = array();
        $pre_str = '';
        for($i = 0; $i < count($str_find); $i++){
            $str = $str_find[$i];
            if($str != $pre_str){
                $start = mb_strpos($subject, $str, $start);
            }else{
                $start = mb_strpos($subject, $str, $start+1);
            }
            $matches[$i] = $start;
            $pre_str = $str;
        }
        return true;
    }else{
        return false;
    }
}
$global_is_test_mode = false;
//echo error log to browser with red color font.
function echo_error($error, $func){
    global $global_is_test_mode;
    if($global_is_test_mode){
        echo '<font color="red">Notice in function ('.$func.'): '.$error.'</font><br>';
    }
}
function echo_notice($error, $func){
    global $global_is_test_mode;
    if($global_is_test_mode){
        echo '<font color="blue">Notice in function ('.$func.'): '.$error.'</font><br>';
    }
}
function echo_ord($row){
    echo 'in function echo ord, row:'.$row.'<br>';
    $char='';
    for($i=0; $i<mb_strlen($row); $i++)
    {
        $char = mb_substr($row, $i, 1);
        echo $i.':-'.$char.'-'.ord($char).'<br>';
    }
}
function preview4test($arr, $role)
{
    echo '<br><font color=red>preview4test</font>['.count($arr).']:';
    //print_r($role);
    if($role == null){
        for($i=0; $i < count($arr); $i++){
            echo '<div>'.$arr[$i].'</div>';
        }
    }else{
        for($i=0; $i < count($arr); $i++){
            echo '<div class='.$role[$i].'>'.$arr[$i].'</div>';
            //echo '<div class='.$role[$i].'>'.$arr[$i].$role[$i].'</div>';
        }
    }
}
?>