<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once("./_common.php");

if ( $_GET['truncate'] == 'truncate' ) {
    sql_query(" TRUNCATE TABLE {$_11street_my_list_table} ");
    sql_query(" TRUNCATE TABLE {$_11street_my_code_table} ");
} else {
    $idxArr = $_POST['idx'];
    foreach ( $idxArr as $key => $idxNumber ) {
        $data = sql_fetch(" SELECT * FROM {$_11street_my_list_table} WHERE idx='".$idxNumber."' ");

        sql_query(" DELETE FROM {$_11street_my_list_table} WHERE idx='".$idxNumber."' ");
        sql_query(" DELETE FROM {$_11street_my_code_table} WHERE cust_id='".$data['cust_id']."' ");
    } // end foreach.
} // end if.

goto_url('/11street.my/?mode=list');
exit;
