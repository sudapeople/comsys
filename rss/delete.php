<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once("./_common.php");

if ( $_GET['truncate'] == 'truncate' ) {
    sql_query(" TRUNCATE TABLE {$__TABLE__list} ");
    sql_query(" TRUNCATE TABLE {$__TABLE__data} ");
} else {
    $idxArr = $_POST['chk'];
    foreach ( $idxArr as $user_id => $target ) {
        sql_query(" DELETE FROM {$__TABLE__list} WHERE user_id='".$user_id."' and target='".$target."' ");
        sql_query(" DELETE FROM {$__TABLE__data} WHERE user_id='".$user_id."' and target='".$target."' ");
    } // end foreach.
} // end if.

goto_url('./?mode=list');
exit;
