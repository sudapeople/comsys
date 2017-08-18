<?php
include_once("./_common.php");

$sql = " SELECT * FROM dev_scrap_11street where uidx='".$_GET['idx']."' ";
$result = sql_query($sql);
$cnt = @sql_num_rows($result);
if (!$cnt)
    alert("출력할 내역이 없습니다.");

$info = sql_fetch(" select * from dev_11street where idx='".$_GET['idx']."' ");

//header('Content-Type: text/x-csv');
header("Content-charset=utf-8");
header('Content-Type: doesn/matter');
header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Content-Disposition: attachment; filename="'. $info['name'] . '_code_' . date("ymdHis", time()) . '.csv"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
//echo iconv('utf-8', 'euc-kr', "우편번호,주소,이름,전화1,전화2,상품명,수량,선택사항,배송비,상품코드,주문번호,운송장번호,전하실말씀\n");

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $row = array_map('iconv_euckr', $row);

    echo $row['code'];
    echo "\n";
}
if ($i == 0)
    echo '자료가 없습니다.'.PHP_EOL;

exit;


