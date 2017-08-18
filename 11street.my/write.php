<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once("./_common.php");

function curl_post_async($uri, $params) {
    $command = "curl ";
    foreach ($params as $key => &$val)
            $command .= "-F '$key=$val' ";
    $command .= "$uri -s > /dev/null 2>&1 &";
    passthru($command);
} // end function.

$direct = $_GET['direct'];
$cust_id = trim($_POST['cust_id']);
$apikey = trim($_POST['ApiKey']);
$target_url = trim($_POST['target_url']);
$target_count = trim($_POST['target_count']);
$target_count = preg_replace("/[^0-9]*/s", "", $target_count);

// apikey 저장
// 중복검사 cust_id -> 입력
$custInfoChk = sql_fetch(" SELECT count(*) as cnt FROM {$_11street_my_cust_info_table} WHERE cust_id='".$cust_id."' ");
$chkCount = $custInfoChk['cnt'];
if ( $chkCount > 0 ) {
    sql_query(" UPDATE {$_11street_my_cust_info_table} SET 11street_my_key='".$apikey."' ");
} else {
    sql_query(" INSERT {$_11street_my_cust_info_table} SET cust_id='".$cust_id."' , 11street_my_key='".$apikey."' , rdate=now() ");
} // end if.

//sql_query(" INSERT {$_insert_table} SET cust_id='".$_POST['cust_id']."' , target_url='".$_POST['target_url']."' , target_count='".$_POST['target_count']."' , rdate=now() ");
sql_query(" INSERT {$_11street_my_list_table} SET cust_id='".$cust_id."' , target_url='".$target_url."' , target_count='".$target_count."' , rdate=now() ");

if ( $direct == 'true' ) {
    $url = 'http://comsys.co.kr/11street.my/scrap.php';
    $params = array(
        //"manual" => "manual",
    );

    curl_post_async($url,$params);
    exit;
} else {
    goto_url('/11street.my/?mode=list');
}
