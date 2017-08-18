<?php
include_once("./_common.php");

if ( !$chk ) alert("잘못된 접근입니다.");

foreach ( $chk as $key => $cid ) {

    $chkSQL = " SELECT * FROM {$_db_navercafe_list} WHERE cid='{$cid}' ";
    $chkRES = sql_fetch($chkSQL);

    $cafe_id = $chkRES['cafe_id'];
    $member_id = $chkRES['member_id'];

    sql_query(" DELETE FROM {$_db_navercafe_scrap_schedule} WHERE member_id='{$member_id}' AND cafe_id='{$cafe_id}' ");
    sql_query(" DELETE FROM {$_db_navercafe_except_list} WHERE member_id='{$member_id}' AND cafe_id='{$cafe_id}' ");
    sql_query(" DELETE FROM {$_db_navercafe_scrap_data} WHERE member_id='{$member_id}' AND cafe_id='{$cafe_id}' ");
    sql_query(" DELETE FROM {$_db_navercafe_list} WHERE cid='{$cid}' ");

} // end foreach.

goto_url("/m/naver/");
