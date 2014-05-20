<?php
function do_html_header($title) {
    // print an HTML header
    ?>
<html>
<head>
<title><?php echo $title;?></title>
<link rel="stylesheet" type="text/css" href="exam-parser.css">
<script type="text/javascript" src="exam-parser.js"></script>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<body>
<?php
}
function do_index_body() {
    ?>
<div class="index">
    <div class=index-form>
		<form method="post" action="parser-exam.php">
            <div class="index-type">
    			<label for="exam-type">题目类型：&nbsp</label> 
    			<select id="exam-type"
    				name="exam-type" class="exam-type">
    				<option value="objective-exam">单选、多选、判断、填空</option>
    				<option value="subjective-exam">综合题</option>
    			</select><span class=noitce>&nbsp[注意：输入的内容要与题型对应]</span>
			</div>

			<textarea id='content' name="content" rows=25><?php 
			if(isset($_SESSION['content'])){print_r($_SESSION['content']);}?></textarea>
			<div class=footer_button>
				<!--input type="submit" name="get_results_array" value="查看结果<Array格式>"-->
				<div class=button_box> 
				<input class="preview" type="submit" name="preview_results" value="查看结果<HTML格式>"></input>
				</div>
				<div class=button_box> 
				<input class="test_view" type="submit" name="view_test_output" value="预览<测试用>"></input>
				</div>
			</div>
		</form>
    </div>
		<div id=notice-title>须知 V</div>
		<div id=notice-content>
			<div class=element>
				<div class=title>0.关于题型</div>
				输入文本框中的题型要与上面列表项中的题型对应<br>
			</div>
			<div class=element>
				<div class=title>1.关于题目</div>
				正则表达式：'^ *(?:[0-9]+ *[\.:]|第[0-9]+题 *[\.:]|第[0-9]+题 *[^\.:]|< *[0-9]+ *> *[\.:]).+'<br>
				解释：数字后跟.或:；第[0-9]+题的形式开头，后面跟.或:；以<数字>开头，后跟.或:<br>
			</div>
			<div class=element>
				<div class=title>2.关于选项</div>
				正则表达式：'^(?: *[A-Fa-f][\.:]| *[0-9]\)|[A-Fa-f][^A-Fa-f\.:]).+'<br>
				解释：以字符A-F或a-f或[0-9])开头<br> 
			</div>
			<div class=element>
				<div class=title>3.关于答案</div>
				正则表达式：'^ *(?:[0-9]+ *[\.:]|< *[0-9]+ *> *[\.:])*\[*(?:参考答案|正确答案|标准答案|正确答案|答案|本题正确答案为|答案及解析|答案)\]*(?::.+?|[^:].+?)'<br>
				解释：前面为数字或被<>括起来的数字（也可以没有），后跟关键字：参考答案、正确答案、标准答案、正确答案、答案、本题正确答案为、答案及解析、答案，关键字可以被[]括起（也可以不括）<br>
			</div>
			<div class=element>
				<div class=title>4.关于解析</div>
				正则表达式：'^ *\[*(?:试题解析|参考解析|答案解析|本题分析|试题点评|本题来源|本题考点|本题解析|解析)\]*(:.+|[^:].+)'<br>
				解释：以关键字：试题解析、参考解析、答案解析、本题分析、试题点评、本题来源、本题考点、本题解析、解析开头，关键字可以被[]括起（也可以不括）<br>
			</div>
			<div class=element>
				<div class=title>5.关于题目与答案分离</div>
				正则表达式：'/^ *([0-9]+ *\.* *[A-Fa-f]+)$/'<br>
				如果有多选，需要使用'0-9.'的形式将答案分开。全是单选则没有限制。<br> 
				题目的个数要与答案的个数相匹配，否则不会加入数组。<br> 
			</div>
			<div class=element>
				<div class=title>6.如果题目与[选项|答案|分析]在同一行</div>
				选项、答案、分析的标记（即对应的正则表达式）前需要有空格；选项[A-F]后如果有'.'则不需要空格。<br> 
			</div>
			<div class=element>
				<div class=title>7.关于综合题</div>
				正则表达式：'^ *([0-9]+ *\. *\[综合题.*?\]| *\[综合题.*?\])'<br> 
				解释：需要以[综合题]的形式开头（[综合题]可以有'数字.'的形式）<br> 
			</div>
		</div>
</div>
<?php
}
function do_html_footer() {
    // print an HTML footer
    ?>
  </body>
</html>
<?php
}
