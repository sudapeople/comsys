<?php
include_once("./_common.php");
include_once(G5_LIB_PATH."/mailer.lib.php");

//if ( $is_admin != 'super' ) alert("공사중");

function curl_post_async($uri, $params) {
    $command = "torsocks curl ";
    foreach ($params as $key => &$val)
            $command .= "-F '$key=$val' ";
    $command .= "$uri -s > /dev/null 2>&1 &";
    passthru($command);
} // end function.

###
## page 총 1,000 페이지까지만 수집 가능
###

####################

$t[] = date("Y-m-d H:i:s", time() );

$member_id = $member['mb_id'];
if ( !$member_id ) {
    alert("잘못된 접근");
    exit;
} // end if.

###
## page 총 1,000 페이지까지만 수집 가능
###

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
    $str = str_replace(array("&", "<", ">", '"', "'"), array("&amp;", "&lt;", "&gt;", "``", "`"), $str);
    return $str;
} // end function.

function specialchars_replace_return($str, $len=0) {
    if ($len) {
        $str = substr($str, 0, $len);
    }
    //$str = str_replace(array("&#63;", "&amp;", "&lt;", "&gt;", "``", "`"), array("?", "&", "<", ">", '"', "'"), $str);
	$str = str_replace(array("^","#"), array("?","&"), $str);
    return $str;
} // end function.

#############################################

$sql = " SELECT * FROM {$_db_navercafe_scrap_schedule} WHERE member_id='{$member_id}' ORDER BY RAND() ";
$res = sql_query($sql);
$chkNUM = sql_num_rows($res);

#############################################

$g5['title'] = '아이디 추출';
include_once("../../_head.php");
?>

<style>
    .pdt10 {padding-top: 10px;}
    .pdt30 {padding-top: 30px;}
    .pdt50 {padding-top: 50px;}

    .pd10 {padding: 10px;}
    .pd30 {padding: 30px;}
    .pd50 {padding: 50px;}

    .center {text-align: center;}
    .frame { width: 100%; height: 400px; }
</style>

<form name="frm" method="post" target="result" action="result.php">
    <input type="hidden" name="scrap" value="start" />
    <input type="hidden" name="member_id" value="<?php echo $member_id; ?>" />

<table cellpadding="0" cellspacing="0">
<tr style="height: 40px; background: #f0f0f0;">
    <th style="width:40px;">NO</th>
    <th style="width:80px;">아이디</th>
    <th style="width:80px;">카페 ID</th>
    <th style="width:200px;">카페명</th>
    <th style="width:200px;">카테고리명</th>
    <!-- <th style="width:120px;">타겟 URL</th> -->
    <th style="width:80px;">상태</th>
</tr>
<?php

if ( $chkNUM > 0 ) {

$num = $chkNUM;
for ( $i=0; $row=sql_fetch_array($res); $i++ ) {

    $cid = $row['cid'];
    $member_id = $row['member_id'];
    $cafe_id = $row['cafe_id'];
        $cafe_URL = 'http://cafe.naver.com/'.$cafe_id;
    $cafe_title = $row['cafe_title'];
    $cate_title = $row['cate_title'];
    $target_url = $row['target_url'];
    $rdate = $row['rdate'];
    $udate = $row['udate'];
    $is_scrap = $row['is_scrap'];

    $view_link = 'http://cafe.naver.com/'.$cafe_id.$target_url.'&search.page=1&userDisplay=50';

?>
<tr style="height: 50px;">
    <td class="center"><?php echo $num; ?></td>
    <td class="center"><?php echo $member_id; ?></td>
    <td class="center"><a href="<?php echo $cafe_URL; ?>" target="_blank"><?php echo $cafe_id; ?></a></td>
    <td class="center"><a href="<?php echo $cafe_URL; ?>" target="_blank"><?php echo $cafe_title; ?></a></td>
    <td class="center"><a href="<?php echo $view_link; ?>" target="_blank"><?php echo $cate_title; ?></a></td>
    <!-- <td><a href="<?php echo $view_link; ?>" target="_blank"><?php echo $target_url; ?></a></td> -->
    <td class="center"><?php echo $is_scrap; ?></td>
</tr>
<tr><td colspan="100" style="border-top: 1px dashed #555;"></td></tr>
<?php
$num--;
} // end for.

?>
<tr><td style="height:10px;"></td></tr>
<tr>
    <td colspan="100"><input type="submit" value="추출 시작" class="pd10" /></td>
</tr>
<?php

} else {
?>
<tr>
    <td colspan="100" class="center" style="height:200px;">데이터 없음.</td>
</tr>
<?php
} // end if.
?>
</table>
</form>

<p class="pdt30"></p>

<section id="notice">
    <p><h5>수집 최대 1,000 페이지까지 됩니다. 아래 창 최하단 스크롤 내리셔서 OK 메시지가 있으면 수집 완료입니다.</h5></p>
    <p>수집 중지를 원할 경우, 키보드의 'ESC' 키를 누르시면 됩니다.</p>
</section>

<p class="pdt10"></p>

<iframe src="result.php" class="frame" id="result" name="result"></iframe>

<p class="pdt50"></p>

<?php
include_once("../../_tail.php");

$t[] = date("Y-m-d H:i:s",time() );

$mailToName = '이성희';
$mailTo = 'bluewing83@gmail.com';
$mailSubject = '[COMSYS 마케팅] 네이버 카페 아이디 추출 완료';
$mailContent = implode("<br/>\n",$t);
//mailer($mailToName, $mailTo, $mailTo, $mailSubject, $mailContent, 1);

exit;
