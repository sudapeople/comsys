<?php
include_once("./_common.php");

// 특수문자 변환
function un_specialchars_replace($str, $len=0) {
    if ($len) {
        $str = substr($str, 0, $len);
    }
    $str = str_replace( array("&amp;", "&lt;", "&gt;", "`") , array("&", "<", ">", "'") , $str);
    return $str;
} // end function.

$type = $_GET['type'];
$member_id = $member['mb_id'];

/* ** TYPE
    naver
    daum
    naver_cafe
    naver_blog
    naver_post
*/

if ( $type == 'all') {
    $query_where_type = '';
} else {
    $query_where_type = " AND engine='{$type}' ";
} // end if.

$sql = " SELECT * FROM {$_db_data} WHERE member_id='$member_id' {$query_where_type} ORDER BY engine ASC , keyword ASC , category ASC , ranking ASC ";
//if ( $is_admin == 'super' ) echo '<p>'.$sql.'</p>'; exit;
$res = sql_query($sql);
$resNUM = sql_num_rows($res);
if (!$resNUM)
    alert("출력할 내역이 없습니다.");

$file_name = '검색결과_'.$member_id.'_'.$type.'_'.date("YmdHis", time()).'.csv';

//header('Content-Type: text/x-csv');
header("Content-charset=utf-8");
header('Content-Type: doesn/matter');
header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Content-Disposition: attachment; filename="'.$file_name.'"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

echo iconv('utf-8', 'euc-kr', '"검색엔진","키워드","매칭데이터","검색분류","랭킹","검색일시","소스코드"'."\n");
//echo '"검색엔진","키워드","검색분류","랭킹","소스코드","검색일시"'."\n";

for ($i=0; $row=sql_fetch_array($res); $i++) {
    $row = array_map('iconv_euckr', $row);

    $kid = trim($row['kid']);
    $member_id = trim($row['member_id']);
    $engine = trim($row['engine']);
        if ( $engine == 'm_naver' ) {
            $engine = "(mobile) Naver";
        } // end if.
    $keyword = trim($row['keyword']);
    $match_key = trim($row['match_key']);
    $search_time = trim($row['search_time']);
    $category = trim($row['category']);
        //$category = iconv("utf-8","cp949",$category);
    $ranking = trim($row['ranking']);
    $code = trim($row['code']);
        $code = un_specialchars_replace($code);
        //$code = iconv("utf-8","cp949",$code);
    $rdate = $row['rdate'];

        echo '"'.$engine.'"';
        echo ',"'.$keyword.'"';
        echo ',"'.$match_key.'"';
        echo ',"'.$category.'"';
        echo ',"'.$ranking.'"';
        echo ',"'.$rdate.'"';
        echo ',"'.$code.'"';
        echo "\n";

} // end for.

if ($i == 0)
    echo '자료가 없습니다.'.PHP_EOL;

exit;
