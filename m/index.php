<?php
include_once("./_common.php");

goto_url("/m/naver/");

if ( $is_admin != 'super' ) alert("잘못된 접근입니다.");

include_once("../_head.php");





include_once("../_tail.php");
