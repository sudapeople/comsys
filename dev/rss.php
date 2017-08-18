<?php
include_once('./_common.php');

if ( !$_GET['user'] ) alert_close("잘못된 접근입니다.");

$user = $_GET['user'];

$resMember = sql_fetch(" select * from {$g5['member_table']} where mb_id = '{$user}' ");
$mb_nick = $resMember['mb_nick'];

// 특수문자 변환
function specialchars_replace($str, $len=0) {
    if ($len) {
        $str = substr($str, 0, $len);
    }

    $str = str_replace(array("&", "<", ">"), array("&amp;", "&lt;", "&gt;"), $str);

    return $str;
}

header('Content-type: text/xml');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

echo '<?xml version="1.0" encoding="utf-8" ?>'."\n";
?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
<title><?php echo specialchars_replace($mb_nick); ?></title>
<link><?php echo specialchars_replace(G5_URL.'/dev/rss.php?user='.$user); ?></link>
<image>
	<url><![CDATA[<?php echo G5_IMG_URL; ?>/logo.jpg]]></url>
	<title><![CDATA[<?php echo specialchars_replace($mb_nick); ?>]]></title>
	<link><?php echo specialchars_replace(G5_URL.'/dev/rss.php?user='.$user); ?></link>
</image>
<description><![CDATA[쇼핑몰 상품 리스트]]></description>
<language>ko</language>

<?php

$sortType = array(
	'rss_date DESC'=>10,
#	'it_time DESC'=>1,
#	'it_hit DESC'=>1,
#	'it_id DESC'=>1,
);

foreach ( $sortType as $orderBy => $limitCount ) {

	#$sql = " SELECT * FROM dev_rss WHERE 1 AND rss_member = '{$user}' ORDER BY {$orderBy} LIMIT {$limitCount} ";
	$sql = " SELECT * FROM dev_rss WHERE 1 AND rss_member = '{$user}' ORDER BY {$orderBy} "; # del limit
	$result = sql_query($sql);
	for ($i=0; $row=sql_fetch_array($result); $i++) {

		# shuffle 함수를 써야겠다. 일단.

		#$curlGetTitle = getHtml($row['rss_url']);

		$rssTitleArray = array();
		$rssTitleArray[] = '';
		$rssTitleArray[] = specialchars_replace($row['rss_url']);
		$rssTitleArray[] = 'TimeNo.' . time();

		shuffle($rssTitleArray);

		$rssTitle = array();
		$rssTitle = implode(" ",$rssTitleArray);
		$rssTitle = specialchars_replace($rssTitle);

		$rssDescArray = array();
		$rssDescArray[] = '';
		$rssDescArray[] = specialchars_replace($row['rss_url']);
		$rssDescArray[] = $curlGetTitle;
		$rssDescArray[] = '수다피플';
		$rssDescArray[] = 'TimeNo.' . time();
		$rssDescArray[] = '오픈마켓';
		$rssDescArray[] = '쇼핑몰';
		$rssDescArray[] = '상품URL';
		$rssDescArray[] = '리스트';
		$rssDescArray[] = '[해외]';
		$rssDescArray[] = specialchars_replace($mb_nick);

		shuffle($rssDescArray);

		$rssDescription = array();
		$rssDescription = implode(" ",$rssDescArray);
		$rssDescription = specialchars_replace($rssDescription);

?>

<item>
<title><?php echo $rssTitle ; ?></title>
<link><?php echo specialchars_replace($row['rss_url']); ?></link>
<description><![CDATA[<?php echo $rssDescription ; ?>]]></description>
<dc:creator>관리자</dc:creator>
<?php

# 원래 일자
#$date = $row['rss_date'];
# 접속할 때마다 최신 일자
$date = date("Y-m-d H:i:s");

// rss 리더 스킨으로 호출하면 날짜가 제대로 표시되지 않음
//$date = substr($date,0,10) . "T" . substr($date,11,8) . "+09:00";
$date = date('r', strtotime($date));
?>
<dc:date><?php echo $date; ?></dc:date>
</item>

<?php
	} // end for.
} // end foreach.

echo '</channel>'."\n";
echo '</rss>'."\n";
?>