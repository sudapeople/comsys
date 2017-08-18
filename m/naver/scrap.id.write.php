<?php
include_once("./_common.php");
include_once(G5_LIB_PATH."/mailer.lib.php");

if ( !$cafe_id ) alert("카페ID가 없습니다.");
if ( !$cafe_title ) alert("카페명이 없습니다.");
if ( !$cate_title ) alert("카테고리명이 없습니다.");
if ( !$target_url ) alert("잘못된 접근입니다.");

###
## page 총 1,000 페이지까지만 수집 가능
###

#############################################

$go_url = "/m/naver/scrap.category.php?cafe_id=".$cafe_id."&member_id=".$member_id;

if ( $unscrap ) { // 해제
    sql_query(" DELETE FROM {$_db_navercafe_scrap_schedule} WHERE cid='{$cid}' ");
    sql_query(" DELETE FROM {$_db_navercafe_scrap_data} WHERE member_id='{$member_id}' AND cafe_id='{$cafe_id}' ");
    goto_url($go_url);
} // end if.

$chkSQL = " SELECT * FROM {$_db_navercafe_scrap_schedule} WHERE member_id='{$member_id}' AND cafe_id='{$cafe_id}' AND target_url='{$target_url}' ";
$chkRES = sql_query($chkSQL);
$chkNUM = sql_num_rows($chkRES);

if ( $chkNUM > 0 ) alert("중복 등록");

$cSQL = " SELECT * FROM {$_db_navercafe_scrap_schedule} WHERE member_id='{$member_id}' AND cafe_id='{$cafe_id}' ";
$cRES = sql_query($cSQL);
$cNUM = sql_num_rows($cRES);

if ( $cNUM >= 5 ) {
    alert("등록 초과");
} // end if.

$cate_title = str_replace(array("'",'"'),array("`","``"),$cate_title);

sql_query(" INSERT INTO {$_db_navercafe_scrap_schedule} SET member_id='{$member_id}' , cafe_id='{$cafe_id}' , cafe_title='{$cafe_title}' , cate_title='{$cate_title}' , target_url='{$target_url}' , rdate=now() ");

goto_url($go_url);
