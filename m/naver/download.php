<?php
include_once("./_common.php");

//$type;
//$member_id;

$sql = " SELECT * FROM {$_db_navercafe_scrap_data} WHERE member_id='$member_id' ORDER BY cafe_id ASC , naver_id ASC ";
$res = sql_query($sql);
$resNUM = sql_num_rows($res);
if (!$resNUM)
    alert("출력할 내역이 없습니다.");

//header('Content-Type: text/x-csv');
header("Content-charset=utf-8");
header('Content-Type: doesn/matter');
header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Content-Disposition: attachment; filename="'.$member_id.'_navercafe_'.date("ymdHis", time()).'.csv"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
//echo iconv('utf-8', 'euc-kr', "우편번호,주소,이름,전화1,전화2,상품명,수량,선택사항,배송비,상품코드,주문번호,운송장번호,전하실말씀\n");

if ( $type == 'list_mailchimp' ) {
    echo iconv('utf-8', 'euc-kr', '"Email Address","First Name","Last Name"'."\n");
} // end if.

for ($i=0; $row=sql_fetch_array($res); $i++) {
    $row = array_map('iconv_euckr', $row);

    $cafe_id = $row['cafe_id'];
        $target_url = 'http://cafe.naver.com/'.$cafe_id;
    $naver_id = $row['naver_id'];
        $prt_naver_id = "ID ".$naver_id;
        //$prt_naver_id = preg_replace("/(^0)/","+\\1",$naver_id);
        //$prt_naver_id = preg_replace("/(^00)/","+\\1",$naver_id);
        $email = $naver_id.'@naver.com';
    $naver_nick = $row['naver_nick'];

    if ( $type == 'list' ) {
        echo '"'.$target_url.'"';
        echo ',"'.$email.'"';
        echo "\n";
    } else if ( $type == 'list_mailchimp' ) {
        echo '"'.$email.'"';
        echo ',"'.$prt_naver_id.'"';
        echo ',""';
        echo "\n";
    } // end if.

} // end for.

if ($i == 0)
    echo '자료가 없습니다.'.PHP_EOL;

exit;
