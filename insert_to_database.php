<?php
require_once('output_html.php');
do_html_header("parser-results");

session_start();
echo 'Sorry, function is in building....';
echo '<br>';
echo '<div class="view_result"><a href=\'preview_results.php?type=review\'>返回</a></div>';

do_html_footer();
?>