<?php
include_once("./_common.php");

function scrap($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 바로 출력 없음.
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	$process = curl_exec($ch);
	curl_close($ch);

	return $process;
} // end function.

// 특수문자 변환
function specialchars_replace($str, $len=0) {
    if ($len) {
        $str = substr($str, 0, $len);
    }
    $str = str_replace(array('&', "<", ">", '"', "'"), array("&amp;", "&lt;", "&gt;", "``", "`"), $str);
    return $str;
} // end function.

##########################################################

if ( !$cafe_id ) alert("잘못된 접근입니다.");

$cafe_id = specialchars_replace(trim($cafe_id));

$cSQL = " SELECT * FROM {$_db_navercafe_list} WHERE member_id='".$member['mb_id']."' ";
$cRES = sql_query($cSQL);
$cNUM = sql_num_rows($cRES);

if ( $is_admin != 'super' ) {
    if ( $cNUM >= 10 ) {
        alert("등록 초과");
    } // end if.
} // end if.

$link = 'http://cafe.naver.com/'.$cafe_id;
$source = iconv("cp949","utf-8",scrap($link));

preg_match("@<h1.*>(.*)</h1>@Us",$source,$matchTITLE);
$title = specialchars_replace(trim(array_pop($matchTITLE)));

$chkSQL = " SELECT * FROM {$_db_navercafe_list} WHERE member_id='".$member['mb_id']."' AND cafe_id='{$cafe_id}' ";
$chkRES = sql_query($chkSQL);
$chkNUM = sql_num_rows($chkRES);

if ( $chkNUM > 0 ) { // 중복
    sql_query(" UPDATE {$_db_navercafe_list} SET cafe_title='{$title}' , udate=now() WHERE member_id='".$member['mb_id']."' AND cafe_id='{$cafe_id}' ");
} else { // 신규
    $sql = " INSERT INTO {$_db_navercafe_list} SET member_id='".$member['mb_id']."' , cafe_id='{$cafe_id}' , cafe_title='{$title}' , rdate=now() ";
    sql_query($sql);
} // end if.

goto_url("/m/naver/");
