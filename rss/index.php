<?php
include_once("./_common.php");

$mode = $_GET['mode'];

if ( $mode == 'list' ) {
    include_once("./list.php");
} else if ( $mode == 'write' ) {
    include_once("./write.php");
} else if ( $mode == 'scrap' ) {
    include_once("./scrap.php");
} else if ( $mode == 'order' ) {
    include_once("./order.php");
} else if ( $mode == 'delete' && $is_admin == 'super' ) {
    include_once("./delete.php");
} else {
    alert("잘못된 접근입니다.");
    echo 'Hello~';
    exit;
} // end if.
