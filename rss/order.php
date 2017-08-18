<?php
include_once("./_common.php");

if ( $is_admin <> 'super' ) {
	alert("최고관리자만 접근 가능합니다.");
	goto_url("./?mode=list");
	exit;
} // end if.

function curl_post_async($uri, $params) {
    $command = "curl ";
    foreach ($params as $key => &$val)
            $command .= "-F '$key=$val' ";
    $command .= "$uri -s > /dev/null 2>&1 &";
    passthru($command);
}

$re = $_GET['re'];
if ( $re == 'yes' ) {
    sql_query(" TRUNCATE TABLE {$__TABLE__data} ");
} // end if.

$url = 'http://comsys.co.kr/rss/scrap.php';
$params = array(
    "manual" => "manual",
);

curl_post_async($url,$params);

goto_url("./?mode=list");
