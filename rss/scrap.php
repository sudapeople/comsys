<?php
include_once("./_common.php");
include_once(G5_LIB_PATH."/mailer.lib.php");
include_once("./lib/scrap.lib.php");

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

//////////////////////////////////////////////////////////////////////////////////////////////////

//$sqlTarget = " SELECT * FROM {$__TABLE__target} ORDER BY target DESC ";
$sqlTarget = " SELECT * FROM {$__TABLE__target} ORDER BY RAND() ";
$resTarget = sql_query($sqlTarget);

// mailSet
$mailSetCustIdArr = array();

for ( $ii=0; $rowT=sql_fetch_array($resTarget); $ii++ ) {

	$target = $rowT['target'];
	//if ( $target == 'naverblog' ) continue; // 네이버 블로그 정상 수집 시 주석처리.

	//$sql = " SELECT * FROM {$__TABLE__list} WHERE target='".$target."' ";
	$sql = " SELECT * FROM {$__TABLE__list} WHERE target='".$target."' ORDER BY RAND() ";
	$res = sql_query($sql);

	for ( $i=0; $row=sql_fetch_array($res); $i++ ) {

		$user_id  = $row['user_id'];
		//$target  = $row['target'];
		$mailSetCustIdArr[] = $user_id.'['.$target.']'; // 메일 발송 때만 사용하니까 여기서 수명은 끝.

		$scrapTarget = '';
		switch ( $target ) {
			case 'storefarm':
				$urlBase = "http://storefarm.naver.com";
				$urlParams = "/category/ALL?page=1&st=POPULAR&dt=LIST&size=80&free=false&cp=1";
			    $scrapTarget = $urlBase.'/'.$user_id.$urlParams;
				break;
			case 'naverblog':
				$urlBase = "http://blog.naver.com/prologue/PrologueList.nhn?blogId=";
			    $scrapTarget = $urlBase.$user_id;
				break;
			case 'naverpost':
				$urlBase = "http://post.naver.com/";
			    $scrapTarget = $urlBase.$user_id;
				break;
			case 'tistory':
			    $scrapTarget = 'http://'.$user_id.'.tistory.com/category';
				break;
			default:
				break;
		} // end switch.

		// SCRAP!!!
		$scrapData = scrap($scrapTarget);
		//echo '<xmp>'.$scrapData.'</xmp>';

		if ( $target == 'storefarm' ) {
			action_storefarm( $scrapData , $user_id , $target );
		} else if ( $target == 'naverblog' ) {
			$scrapData = iconv('euc-kr', 'utf-8', $scrapData);
			action_naverblog( $scrapData , $user_id , $target );
		} else if ( $target == 'naverpost' ) {
			$scrapData = iconv('euc-kr', 'utf-8', $scrapData);
			action_naverpost( $scrapData , $user_id , $target );
		} else if ( $target == 'tistory' ) {
			action_tistory( $scrapData , $user_id , $target );
		} else {
			continue;
		} // end if.

		// 마지막 업데이트 datetime 기록
		sql_query(" UPDATE {$__TABLE__list} SET last_scrap_datetime = now() WHERE user_id='".$user_id."' AND target='".$target."' ");

	} // end for.

} // end for.

$mailToName = '이성희';
$mailTo = 'bluewing83@gmail.com';
$mailContent = implode("<br />\n",$mailSetCustIdArr);
$mailSubjectTarget = implode(", ",$mailSetCustIdArr);
$mailSubject = '[RSS] '.$mailSubjectTarget.' DATA 수집 완료.';
mailer($mailToName, $mailTo, $mailTo, $mailSubject, $mailContent, 1);

if ( $manual == 'manual' ) {
    goto_url("./?mode=list");
} else {
    exit;
}
