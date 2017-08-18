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
$target = $_POST['target'];
$user_id = $_POST['user_id'];

$sql = " SELECT * FROM {$__TABLE__list} WHERE user_id='".$user_id."' target='".$target."' ";
$res = sql_query($sql);
$checkCount = sql_num_rows($res);

if ( $checkCount > 0 ) alert("이미 동일한 정보가 있긴 있습니다.");

sql_query(" INSERT {$__TABLE__list} SET user_id='".trim($user_id)."' , target='".$target."' , rdate=now() ");

if ( $direct == 'true' ) {
    $url = 'http://comsys.co.kr/rss/scrap.php';
    $params = array(
        //"manual" => "manual",
    );

    curl_post_async($url,$params);
    exit;
} else {
    goto_url('./?mode=list');
}
