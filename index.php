<?php
require_once('output_html.php');
session_start();
//if(isset($_SESSION['content'])){print_r($_SESSION['content']);}
do_html_header("exam-parser-index");
do_index_body();
do_html_footer();
?>
