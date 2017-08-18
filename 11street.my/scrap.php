<?php
include_once("./_common.php");
include_once(G5_LIB_PATH.'/mailer.lib.php');

function scrap($url,$param='') {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 바로 출력 없음.
	$process = curl_exec($ch);
	curl_close($ch);

	return $process;
}

$sql = " SELECT * FROM {$_11street_my_list_table} WHERE finished = 'no' ORDER BY idx ASC ";
$res = sql_query($sql);

// mailSet
$mailSetCustIdArr = array();

for ( $i=0; $row=sql_fetch_array($res); $i++ ) {

	$idx = $row['idx'];
	$cust_id  = $row['cust_id'];
	$mailSetCustIdArr[] = $cust_id;
	$target_url = $row['target_url'];
    $target_count = $row['target_count'];

    $page = ceil( $target_count / 400 );

	for ( $p=1; $p<=$page; $p++ ) {

		$urlPageParam = "&pageNum=".$p;
		$urlUniParam = "&viewType=I".$urlPageParam."&pageRows=10&pageSize=400";

		$scrap = '';
		$scrap = scrap($target_url.$urlUniParam);

		//preg_match_all("@<a itemprop=\"url\" href=\"([^\"]+)\"@Us",$scrap,$match);
		preg_match_all("@data-trackid=\"([^\"]+)\"@Us",$scrap,$match);
		$scrapUrls = $match[1];

		foreach ( $scrapUrls as $key => $scrapUrl ) {
			//$procUrl1 = explode("-",$scrapUrl);
			//$code = array_pop($procUrl1);
			$code = $scrapUrl;

			// check
			$r = sql_fetch(" SELECT code_number FROM {$_11street_my_code_table} WHERE code_number='".$code."' and cust_id='".$cust_id."' ");
			if ( $r['code_number'] ) {
			} else {
				sql_query(" INSERT INTO {$_11street_my_code_table} SET code_number='".$code."' , cust_id='".$cust_id."' , rdate=now() ");
			}
			//usleep(300000);
		} // end foreach.

	} // end for.

	// 수집이 완료되면 fin_scrap 칼럼 yes 값으로 변경
	sql_query(" UPDATE {$_11street_my_list_table} SET finished='yes' WHERE idx='".$idx."' ");

	//usleep(500000);

} // end for.

$mailToName = '이성희';
$mailTo = 'bluewing83@gmail.com';
$mailSubject = '[11street.my] 상품코드 수집 완료.';
$mailContent = implode("\n",$mailSetCustIdArr);
//mail( $mailTo, $mailSubject, $mailContent );
mailer($mailToName, $mailTo, $mailTo, $mailSubject, $mailContent, 1);

if ( $manual == 'manual' ) {
    goto_url("./?mode=list");
} else {
    exit;
}

//include_once("./_tail.php");
?>
