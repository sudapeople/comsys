<?php
include_once("./_common.php");

/////////////////////////////////////////////////////////////

//$g5['title'] = '11street.my 마이샵 상품코드 수집';
//include_once("./_head.php");

/////////////////////////////////////////////////////////////

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

$sql = " select * from dev_11street order by idx asc ";
$res = sql_query($sql);

for ( $i=0; $row=sql_fetch_array($res); $i++ ) {

	$uidx = $row['idx'];
	$url  = $row['url'];
	$page = $row['page'];

	for ( $p=1; $p<=$page; $p++ ) {

		$urlPageParam = "&pageNum=".$p;
		$urlUniParam = "&viewType=I".$urlPageParam."&pageRows=10&pageSize=400";

		$scrap = '';
		$scrap = scrap($url.$urlUniParam);

		//preg_match_all("@<a itemprop=\"url\" href=\"([^\"]+)\"@Us",$scrap,$match);
		preg_match_all("@data-trackid=\"([^\"]+)\"@Us",$scrap,$match);
		$scrapUrls = $match[1];

		foreach ( $scrapUrls as $key => $scrapUrl ) {
			//$procUrl1 = explode("-",$scrapUrl);
			//$code = array_pop($procUrl1);
			$code = $scrapUrl;

			// check
			$r = sql_fetch(" select code from dev_scrap_11street where code='".$code."' and uidx='".$uidx."' ");
			if ( $r['code'] ) {
			} else {
				sql_query(" insert into dev_scrap_11street set code='".$code."' , uidx='".$uidx."' , rdate=now() ");
			}
			//usleep(300000);
		} // end foreach.

	} // end for.

	// 수집이 완료되면 fin_scrap 칼럼 yes 값으로 변경
	sql_query(" update dev_11street set fin_scrap='yes' where idx='".$uidx."' ");

	//usleep(500000);

} // end for.

//include_once("./_tail.php");
?>