<?php
include_once("./_common.php");

function curl_post_async($uri, $params) {
    $command = "curl ";
    foreach ($params as $key => &$val)
            $command .= "-F '$key=$val' ";
    $command .= "$uri -s > /dev/null 2>&1 &";
    passthru($command);
}

$url = 'http://comsys.co.kr/11street.my/scrap.php';
$params = array(
    "manual" => "manual",
);

curl_post_async($url,$params);

goto_url("./?mode=list");
