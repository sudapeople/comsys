<?php
include_once("./_common.php");
//include_once(G5_LIB_PATH."/mailer.lib.php");

if ( !$member_id || !$cafe_id || !$save_url || !$target_url ) exit;

function curl_post_async($uri, $params) {

    $command = "torsocks curl ";
    foreach ($params as $key => &$val)
            $command .= "-F '$key=$val' ";
    $command .= "$uri -s > /dev/null 2>&1 &";
    passthru($command);

} // end function.

function scrap($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
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
    $str = str_replace(array("&", "<", ">", '"', "'"), array("&amp;", "&lt;", "&gt;", "``", "`"), $str);
    return $str;
} // end function.

#######################
## 제외 아이디 - 매니저
$exRES = sql_fetch(" SELECT * FROM {$_db_navercafe_except_list} WHERE member_id='{$member_id}' AND cafe_id='{$cafe_id}' ");
$manager_id = $exRES['manager_id'];

#######################
$sql = " SELECT * FROM {$_db_navercafe_scrap_schedule} WHERE member_id='{$member_id}' AND cafe_id='{$cafe_id}' AND target_url='{$save_url}' ";
$res = sql_fetch($sql);
$cafe_title = $res['cafe_title'];
$cate_title = $res['cate_title'];
#######################

$ori_source = scrap($target_url);
//$ori_source = curl_post_async($target_url,$params);
$ori_source = iconv("cp949","utf-8",$ori_source);

//echo '<h1>PAGE : '.$page.'</h1><xmp>'.$ori_source.'</xmp>';

preg_match("@<div class=\".*\" id=\"upperArticleList\">(.*)<div class=\"list-search@Us",$ori_source,$matchLIST);
$board_list = array_pop($matchLIST);

preg_match_all("@(<td align=\"left\" class=\"board-list\">.*<td class=\"view-count m-tcol-c\">)@Us",$board_list,$matchCUT);
$content_cut = array_pop($matchCUT);

foreach ( $content_cut as $key => $cutList ) {

    preg_match("@articleid=(.*)['|&]@Us",$cutList,$matchArticleid);
    $articleid = array_pop($matchArticleid);

    preg_match("@onclick=\"ui\(event, '(.*)'@Us",$cutList,$matchCONT);
    $naver_id = array_pop($matchCONT);

    preg_match("@onclick=\"ui\(event, '.*',.*,'(.*)'@Us",$cutList,$matchName);
    $naver_nick = array_pop($matchName);

	if ( $manager_id == $naver_id ) continue;

    $sql = "
        INSERT INTO
            {$_db_navercafe_scrap_data}
        SET
            member_id='$member_id'
            , cafe_id='{$cafe_id}'

            , cafe_title='{$cafe_title}'
            , cate_title='{$cate_title}'

            , naver_id='{$naver_id}'
            , naver_nick='{$naver_nick}'
			, target_url='{$target_url}'
            , rdate=now()
    ";
    sql_query($sql);

    //echo '<p>'.$naver_id.'@naver.com</p>';

} // end foreach.
