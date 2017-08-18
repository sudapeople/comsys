<?php
set_time_limit(0);
ini_set('max_execution_time', 9999999);
ini_set('memory_limit', '2048M');

include_once("./_common.php");
include_once(G5_LIB_PATH."/mailer.lib.php");

function curl_post_async($uri, $params) {

    //$command = "torsocks curl ";
    $command = "curl ";
    foreach ($params as $key => &$val)
            $command .= "-F '$key=$val' ";
    $command .= "$uri -s > /dev/null 2>&1 &";
    passthru($command);

    //echo '<p style="font-size: 12px;">'.$command.'</p>';
} // end function.

###
## page 총 1,000 페이지까지만 수집 가능
###

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

####################

$cont = array();
$t[] = date("Y-m-d H:i:s", time() );

if ( !$member['mb_id'] ) exit;
if ( !$scrap ) exit;

####################

if ( $scrap == 'start' && $member['mb_id'] ) {
    //print_r2($_POST);

    $member_id = $member['mb_id'];
    $sql = " SELECT * FROM {$_db_navercafe_scrap_schedule} WHERE member_id='{$member_id}' AND is_scrap<>'completed' ORDER BY cid ASC ";
    $res = sql_query($sql);
    $chkNUM = sql_num_rows($res);

    if ( $chkNUM > 0 ) {

        for ( $i=0; $row=sql_fetch_array($res); $i++ ) {

            $cid = $row['cid'];
            $member_id = $row['member_id'];
            $cafe_id = $row['cafe_id'];
                $cafe_url = 'http://cafe.naver.com/'.$cafe_id;
            $cafe_title = $row['cafe_title'];
            $target_url = $row['target_url'];
            $rdate = $row['rdate'];
            $udate = $row['udate'];
            $is_scrap = $row['is_scrap'];

            $state_is_scrap = '';
            if ( $i == 0 ) {
                $state_is_scrap = 'scraping';
                sql_query(" UPDATE {$_db_navercafe_scrap_schedule} SET is_scrap='{$state_is_scrap}' , udate=now() WHERE member_id='{$member_id}' AND cafe_id='{$cafe_id}' AND target_url='{$target_url}' ");
            } // end if.

            #######################################
            /*
            $mSQL = " SELECT * FROM {$_db_navercafe_except_list} WHERE member_id='".$member['mb_id']."' AND cafe_id='{$cafe_id}' ";
            $mRES = sql_query($mSQL);
            $mNUM = sql_num_rows($mRES);

            $chkExceptID = array();
            for ( $i=0; $mROW=sql_fetch_array($mRES); $i++ ) {
            	$chkExceptID[] = $mROW['manager_id'];
            } // end for.
            */
            #######################################
            ## 끝 페이지 알아내기
            #######################################

            #######################################

            echo '<p><h4>Message : 페이지 수집 시작</h4></p>';

            ob_flush();
            flush();

            #######################################

            $loop_page = 200;
            $page_num = 1;
            $test_count = 1;

            while ( $page_num <= $loop_page ) {

                $param = '';
                $param .= '&search.page='.$page_num;
                $param .= '&userDisplay=50';
                $chkPageURL = 'http://cafe.naver.com/'.$cafe_id.$target_url.$param;

                $scrap_page_view = iconv("cp949","utf-8",scrap($chkPageURL));

                if ( preg_match("@(class=\"m-tcol-c pn\"><span>다음</span>)@Us",$scrap_page_view) ) {
                    // 다음 페이지가 있는 경우
                    preg_match_all("@<td><a href=\".*\" class=\"m-tcol-c\">(.*)</a></td>@Us",$scrap_page_view,$mPages);
                    $sch_last_page = array_pop(array_pop($mPages));
                    if ( $page_num == 991 ) {
                        // 마지막 페이지
                        preg_match_all("@<td><a href=\".*\" class=\"m-tcol-c\">(.*)</a></td>@Us",$scrap_page_view,$mPages);
                        $sch_last_page = array_pop(array_pop($mPages));
                        $pagenum = $sch_last_page;
                        break;
                    } else {
                        $page_num = $sch_last_page + 1;
                    } // end if.

                    ob_flush();
                    flush();

                    $stime = mt_rand(100000,300000);
                    usleep($stime);

                } else {

                    // 마지막 페이지
                    preg_match_all("@<td><a href=\".*\" class=\"m-tcol-c\">(.*)</a></td>@Us",$scrap_page_view,$mPages);
                    $sch_last_page = array_pop(array_pop($mPages));
                    $pagenum = $sch_last_page;
                    break;

                } // end if.

                $test_count++;
            } // end while.

            /*
            $t[] = date("Y-m-d H:i:s", time() );
            $process_time = implode(" || ",$t);
            echo '<p>'.$process_time.'</p>';
            echo '<p>'.$pagenum.'</p>';
            exit;
            */

            #######################################

            echo '<p><h5>Message : '.$cafe_title.' / '.$cafe_url.' : 페이지 수집 완료</h5></p>';
            echo '<p><h5>Message : '.$cafe_title.' / '.$cafe_url.' : 아이디 수집 시작</h5></p>';
            ob_flush();
            flush();

            #######################################

            if ( !$pagenum ) $pagenum = 1000;
            $page = 1;
            while ( $page <= $pagenum ) {

                $param = '';
                $param .= '&search.page='.$page;
                $param .= '&userDisplay=50';
                $save_url = $target_url;
                $scrap_url = 'http://cafe.naver.com/'.$cafe_id.$target_url.$param;

                #############################################

                $url = 'http://comsys.co.kr/m/naver/scrap.php';

                $params = array();
                $params['member_id'] = $member_id;
                $params['cafe_id'] = $cafe_id;
                $params['save_url'] = $save_url;
                $params['target_url'] = $scrap_url;

                curl_post_async($url,$params);

                echo '<p style="font-size: 12px;">PAGE : '.$page.'</p>';

                ob_flush();
                flush();

                $stime = mt_rand(100000,1000000);
                usleep($stime);

                #############################################

                $page++;
            } // end while.

            sql_query(" UPDATE {$_db_navercafe_scrap_schedule} SET udate=now() , is_scrap='completed' WHERE cid='{$cid}' ");
            echo '<p><h5>Message : '.$cafe_title.' / '.$cafe_url.' / '.number_format($pagenum).' 페이지 수집완료. OK</h5></p>';
            ob_flush();
            flush();

            $cont[$i]['cafe_title'] = $cafe_title;
            $cont[$i]['cafe_url'] = $cafe_url;

        } // end for.

    } // end if.

    echo '<p><h2>Message : OK</h2></p>';

    $content = '';
    $cnum = 1;
    foreach ( $cont as $key => $ct ) {
        $content .= $cnum.'. '.$ct['cafe_title'].' '.$ct['cafe_url']."\n<br/>";
        $cnum++;
    } // end foreach.

    $t[] = date("Y-m-d H:i:s", time() );
    $mailToName = 'COMSYS';
    //$mailTo = 'bluewing83@gmail.com';
    //$mailTo = '';
    $mailToArr = array();
    if ( $member['mb_email'] == 'bluewing83@gmail.com' ) {
        $mailToArr = array($member['mb_email']);
    } else {
        $mailToArr = array('bluewing83@gmail.com',$member['mb_email']);
    } // end if.
    $mailSubject = '[COMSYS 마케팅] 네이버 카페 아이디 추출 완료';
    $mailContent = implode("<br/>\n",$t);
    $mailContent .= "<br/><br/>\n".$content;
    foreach ( $mailToArr as $key => $mailTo ) {
        if ( $mailTo == 'bluewing83@gmail.com' ) $mailSubject = $mailSubject.' - '.implode("|",$mailToArr);
        mailer($mailToName, $mailTo, $mailTo, $mailSubject, $mailContent, 1);
    } // end foreach.

} // end if

##########################################

exit;
