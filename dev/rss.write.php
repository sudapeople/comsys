<?php
include_once("./_common.php");
include_once(G5_LIB_PATH.'/mailer.lib.php');

if ( !$_POST['rss_url'] ) alert("잘못된 접근입니다.");

$rss_url = $_POST['rss_url'];

$member_id = $member['mb_id'];
/*
if ( $is_admin ) {
	$member_id = 'level3';
}
*/

/* 처음 방법 */
/*
$sqlCf = " select * from dev_config where cf_id = 1 ";
$resCf = sql_fetch($sqlCf);
$cf_cnt = $resCf['cf_rss_add_count'];
*/

/* 신규방법 g5_member 테이블 mb_1 칼럼의 값 체크 */
$sqlCf = " select * from {$g5['member_table']} where mb_id = '{$member_id}' ";
$resCf = sql_fetch($sqlCf);
$cf_cnt = $resCf['mb_1'];

$sqlCnt = " select count(*) as cnt from dev_rss where 1 and rss_member = '{$member_id}' ";
$resCnt = sql_fetch($sqlCnt);
$mb_rss_count = $resCnt['cnt'];

if ( !$cf_cnt ) {
	if ( !$is_admin ) if ( $cf_cnt <= $mb_rss_count ) alert("URL 등록수 초과");
} else {
	if ( $cf_cnt <= $mb_rss_count ) alert("URL 등록수 초과");
}

$sql = " insert into dev_rss set
	rss_url = '{$rss_url}',
	rss_member = '{$member_id}',
	rss_date = now()
";
sql_query($sql);

# 첫 번째 URL 등록 시 이메일 발송
# 현재는 URL 등록 후 카운트가 1 일 때만 메일을 발송하도록 해놨다.
# 다른 조건이 생각나면 개발하자.

$cntUrlRes = sql_fetch(" select count(*) as cnt from dev_rss where rss_member = '{$member_id}' ");
#alert($cntUrlRes['cnt']);

$subject = "[개발] ".$member_id." 맴버 URL 첫 등록 개시";
$content = "dev.comsys.co.kr ".$resCf['mb_name']."(".$member_id.") 맴버가 RSS URL 등록을 시작했습니다.";

if ( $cntUrlRes['cnt'] == 1 ) {
	mailer('개발', 'april.15.friday@gmail.com', 'bluewing83@gmail.com', $subject, $content, 1);
}

# move!!
goto_url('./rss.list.php');

?>
