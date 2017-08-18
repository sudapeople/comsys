<?php
include_once('./_common.php');

// 특수문자 변환
function specialchars_replace($str, $len=0) {
    if ($len) {
        $str = substr($str, 0, $len);
    }

    $str = str_replace(array("&", "<", ">"), array("&amp;", "&lt;", "&gt;"), $str);

    /*
    $str = preg_replace("/&/", "&amp;", $str);
    $str = preg_replace("/</", "&lt;", $str);
    $str = preg_replace("/>/", "&gt;", $str);
    */

    return $str;
}

// RSS 사용 체크
if ( $_GET['view'] != 'rss' ) {
    echo 'RSS 보기가 금지되어 있습니다.';
    exit;
}

header('Content-type: text/xml');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

echo '<?xml version="1.0" encoding="utf-8" ?>'."\n";
?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
<title><?php echo specialchars_replace($config['cf_title']); ?></title>
<link><?php echo specialchars_replace(G5_SHOP_URL.'/shop.rss.php?view='.$view); ?></link>
<image>
	<url><![CDATA[<?php echo G5_DATA_URL; ?>/common/logo_img]]></url>
	<title><![CDATA[<?php echo specialchars_replace($config['cf_title']); ?>]]></title>
	<link><?php echo specialchars_replace(G5_SHOP_URL.'/shop.rss.php?view='.$view); ?></link>
</image>
<description><![CDATA[쇼핑몰 상품 리스트]]></description>
<language>ko</language>

<?php

$sortType = array(
	'it_update_time DESC'=>10,
#	'it_time DESC'=>1,
#	'it_hit DESC'=>1,
#	'it_id DESC'=>1,
);

foreach ( $sortType as $orderBy => $limitCount ) {

	$sql = " SELECT it_id, it_name, it_explan, it_explan2, it_time, it_use, it_img1, it_hit
				FROM {$g5['g5_shop_item_table']}
				WHERE
				1
				AND it_use not like '%0%'
				ORDER BY {$orderBy} LIMIT 0, {$limitCount} ";
	$result = sql_query($sql);
	for ($i=0; $row=sql_fetch_array($result); $i++) {
		$file = '';

		if (strstr($row['it_use'], 'html'))
			$html = 1;
		else
			$html = 0;

		$imgURL = '';
		if ( !$row['it_img1'] ) 
			$imgURL = G5_SHOP_URL.'/img/no_image.gif' ;
		else
			$imgURL = G5_DATA_URL.'/item/'.$row['it_img1'];

		$imagefileurl = $imgURL;
		$file = $imagefileurl;

	$description = strip_tags($row['it_explan']);

?>

<item>
<title><?php echo specialchars_replace($row['it_name']) . ' TimeNo.' . time() ; ?></title>
<link><?php echo specialchars_replace(G5_SHOP_URL.'/item.php?it_id='.$row['it_id'].'&time='.time()); ?></link>
<description><![CDATA[<img src="<?php echo $file; ?>" alt="<?php echo specialchars_replace($row['it_name']); ?>"><?php echo conv_content($description, $html); ?>]]></description>
<dc:creator>관리자</dc:creator>
<?php
$date = $row['it_time'];
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
