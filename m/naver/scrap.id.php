<?php
include_once("./_common.php");
include_once(G5_LIB_PATH."/mailer.lib.php");

if ( !$cafe_id ) alert("잘못된 접근입니다.");
if ( !$url ) alert("잘못된 접근입니다.");

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

$mSQL = " SELECT * FROM {$_db_navercafe_except_list} WHERE member_id='".$member['mb_id']."' AND cafe_id='{$cafe_id}' ";
$mRES = sql_query($mSQL);
$mNUM = sql_num_rows($mRES);

$chkExceptID = array();
for ( $i=0; $mROW=sql_fetch_array($mRES); $i++ ) {
	$chkExceptID[] = $mROW['manager_id'];
} // end for.

#############################################

//$url = specialchars_replace_return($url);
$ori_source = scrap($url);
$ori_source = iconv("cp949","utf-8",$ori_source);

//echo '<xmp>'.iconv("cp949","utf-8",$ori_source).'</xmp>';

preg_match("@<div class=\".*\" id=\"upperArticleList\">(.*)<div class=\"list-search@Us",$ori_source,$matchLIST);
$board_list = array_pop($matchLIST);

//echo '<xmp>'.$board_list.'</xmp>';

preg_match_all("@onclick=\"ui\(event, '(.*)'@Us",$board_list,$matchCONT);
$list_id = array_unique(array_pop($matchCONT));

$g5['title'] = '아이디 추출';
include_once("../../_head.php");
?>

<style>
    .center {text-align: center;}
    .pd10 {padding-top: 10px;}
	.pd20 {padding-top: 20px}
	.pd50 {padding-top: 50px}
</style>

<p>아이디 중복과 매니저 아이디는 제거된 결과입니다.</p>
<p><a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a></p>

<p class="pd20"></p>

<table cellspacing="0">
<tr style="background: #f0f0f0;">
    <th style="width:40px; height:40px;">NO</th>
    <th style="width:100px;">아이디</th>
    <th style="width:200px;">이메일 주소</th>
    <th style="width:200px;">블로그 주소</th>
</tr>
<?php
$num = 1;
foreach ( $list_id as $k => $user_id ) {

	if ( in_array($user_id,$chkExceptID) ) continue;

    $url_blog = 'http://blog.naver.com/'.$user_id;
    $url_email = $user_id.'@naver.com';

?>
<tr>
    <td class="center" style="height:30px;"><?php echo $num; ?></td>
    <td class="center"><?php echo $user_id; ?></td>
    <td class=""><?php echo $url_email; ?></td>
    <td class=""><a href="<?php echo $url_blog; ?>" target="_blank"><?php echo $url_blog; ?></a></td>
</tr>
<tr><td colspan="100" style="height: 1px; background: #fafafa;"></td></tr>
<?php
$num++;
} // end foreach.
?>
</table>

<p class="pd50"></p>

<?php
include_once("../../_tail.php");
