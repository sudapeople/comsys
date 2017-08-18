<?php
include_once("./_common.php");

if ( !$_POST['rss_id'] ) alert("잘못된 접근입니다.");

$rss_id = $_POST['rss_id'];

sql_query(" delete from dev_rss where rss_id = '{$rss_id}' ");

goto_url('./rss.list.php');

$g5['title'] = '개발공간';
#include_once("../_head.php");
?>


<?php
#include_once("../_tail.php");
?>
