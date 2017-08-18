<?php
include_once("./_common.php");
//include_once(G5_LIB_PATH."/mailer.lib.php");

// 특수문자 변환
function specialchars_replace($str, $len=0) {
    if ($len) {
        $str = substr($str, 0, $len);
    }
    $str = str_replace(array("&", "<", ">"), array("&amp;", "&lt;", "&gt;"), $str);
    return $str;
} // end function.

$mode = $_GET['mode'];
$user_id = $_GET['user_id'];
$pure = $_GET['pure'];
$target = $_GET['target'];
if ( !$target ) $target = 'storefarm';

// all or user_id
$_is_mode_ = false;
if ( $mode == 'all' ) {
    $_is_mode_ = true;
} // end if.

$_is_pure_ = false ;
if ( $pure == 'yes' ) {
    $_is_pure_ = true ;
} // end if.

if ( $_is_mode_ === false ) {
    if ( !$user_id ) alert("잘못된 접근입니다.");
} // end if

$_in_exceptUser_ = array(
    //'gbseller808',
    //'yahanuni',
    'global0290',
    'nyseller',
    '4you4you',
    'eeh0309',
    //'hktailor3111',
    'buledyablue',
    'monghwa',
    'okis',
);

$dataWhere = " user_id = '".$user_id."' "; // WHERE 시작.
$exceptUser = '';
if ( $_is_mode_ === true ) {
    $dataWhere = " 1 ";
    $exceptUserArr = array();
    foreach ( $_in_exceptUser_ as $key => $user ) {
        $exceptUserArr[] = " user_id != '".$user."' ";
    } // end foreach.
    $exceptUser = implode(" AND ",$exceptUserArr);
    $dataWhere = $exceptUser;
} // end if

$sql = " SELECT * FROM {$__TABLE__list} WHERE user_id='".$user_id."' AND target='".$target."' ";
$rowDataBase = sql_fetch($sql);

if ( $_is_mode_ === false ) {

    $title = $rowDataBase['title'];
    $mainURL = G5_URL.'/rss/rss.php?user_id='.$user_id.'&amp;target='.$target;

} else {

    $title = 'COMSYS RSS';
    $mainURL = G5_URL;

} // end if

#$date = $row['rss_date'];
# 원래 일자
# 접속할 때마다 최신 일자
$date = date("Y-m-d H:i:s");

// rss 리더 스킨으로 호출하면 날짜가 제대로 표시되지 않음
//$date = substr($date,0,10) . "T" . substr($date,11,8) . "+09:00";
$date = date('r', strtotime($date));

header('Content-type: text/xml');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
echo '<?xml version="1.0" encoding="utf-8" ?>'."\n";
?>
<rss version="2.0">
    <channel>
        <title><?php echo specialchars_replace($title); ?></title>
        <link><?php echo specialchars_replace($mainURL); ?></link>
        <description><?php echo $title; ?></description>
        <language>ko-KR</language>
        <pubDate><?php echo $date ; ?></pubDate>
        <generator>ATOM</generator>
        <managingEditor><![CDATA[수다피플]]></managingEditor>
        <image>
            <title><![CDATA[수다피플]]></title>
            <url><?php echo G5_IMG_URL; ?>/logo.jpg</url>
            <link><?php echo $mainURL; ?></link>
            <description>COMSYS RSS PAGE</description>
        </image>
<?php
$sortType = array(
	'rdate DESC'=>20,
#	'it_time DESC'=>1,
#	'it_hit DESC'=>1,
#	'it_id DESC'=>1,
);

foreach ( $sortType as $orderBy => $limitCount ) { // 어차피 지금은 한 번 출력.

/*
    $orderbyLimit = " ORDER BY ".$orderBy." LIMIT ".$limitCount;
    if ( $_is_mode_ === true ) {
        $orderbyLimit = " ORDER BY target ASC , ".$orderBy;
    } // end if
*/

    $queryOrderby = " ORDER BY RAND() ";
    $queryLimit = " LIMIT ".$limitCount;

	#$sql = " SELECT * FROM dev_rss WHERE 1 AND rss_member = '{$user}' ORDER BY {$orderBy} "; # del limit
    $sql = " SELECT * FROM {$__TABLE__data} WHERE ".$dataWhere.$queryOrderby.$queryLimit;
	$result = sql_query($sql);
	for ($i=0; $row=sql_fetch_array($result); $i++) {

        $target_url = $row['target_url'];
        if ( $_is_pure_ === true ) {

            $rssTitle = specialchars_replace($row['target_subject']);
            $rssDescription = specialchars_replace($row['target_desc']);
            $target_url = $row['target_url'];
            $date = date('r', time());

        } else {

            $rssTitleArray = array();
            $rssTitleArray[] = $row['target_subject'];
            $rssTitleArray[] = '[문의주세요]';
            //$rssTitleArray[] = '구경만 해보세요.';
    		//$rssTitleArray[] = specialchars_replace($row['target_url']);
            //$rssTitleArray[] = '수다피플';
    		//$rssTitleArray[] = '#' . substr(time(),-4);

    		shuffle($rssTitleArray);

    		$rssTitle = array();
    		$rssTitle = implode(" ",$rssTitleArray);
    		$rssTitle = specialchars_replace($rssTitle);

            ####

            // 컨텐츠별 대표 image
            $contentImgTag = '<p><img src="'.$row['target_img'].'" /></p>';
            $contentImg = $row['target_img'];

    		$rssDescArray = array();
            $rssDescArray[] = $row['target_subject'];
    		$rssDescArray[] = $row['target_url'];
            $rssDescArray[] = $row['target_desc'];
    		//$rssDescArray[] = '수다피플';
            //$rssDescArray[] = 'SUDAPEOPLE';
            $rssDescArray[] = '궁금하세요?';
            $rssDescArray[] = '[문의주세요]';
            //$rssDescArray[] = '';
            //$rssDescArray[] = '스토어팜';
            //$rssDescArray[] = '#' . substr(time(),-4);

    		shuffle($rssDescArray);

    		$rssDescription = array();
    		$rssDescription = implode(" ",$rssDescArray);
    		$rssDescription = specialchars_replace($rssDescription);

            $rssDescription = $contentImgTag.$rssDescription;

            $target_url = $target_url.'#'.substr(time(),-4);

            # 원래 일자
            #$date = $row['rss_date'];
            # 접속할 때마다 최신 일자
            $plus = mt_rand(1,9);
            $date = date("Y-m-d H:i:s",time()-60*$plus);

            // rss 리더 스킨으로 호출하면 날짜가 제대로 표시되지 않음
            //$date = substr($date,0,10) . "T" . substr($date,11,8) . "+09:00";
            $date = date('r', strtotime($date));
        } // end if.

?>
        <item>
            <title><![CDATA[<?php echo $rssTitle; ?>]]></title>
            <link><?php echo specialchars_replace($target_url); ?></link>
            <category>NaverBlog</category>
            <author><![CDATA[수다피플]]></author>
            <guid><?php echo specialchars_replace($target_url); ?></guid>
            <pubDate><?php echo $date ; ?></pubDate>
            <description><![CDATA[ <?php echo $rssDescription ; ?> ]]></description>
        </item>
<?php
	} // end for.
} // end foreach.

echo '  </channel>'."\n";
echo '</rss>'."\n";

/*
$mailToName = '이성희';
$mailTo = 'bluewing83@gmail.com';
$mailSubject = '[RSS] '.$user_id.' 페이지 접속이 있었습니다.';
$mailSetCustIdArr = array();
$mailSetCustIdArr[] = $_SERVER['HTTP_USER_AGENT'];
$mailContent = implode("\n",$mailSetCustIdArr);
mailer($mailToName, $mailTo, $mailTo, $mailSubject, $mailContent, 1);
*/
