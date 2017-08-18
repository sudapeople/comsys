<?php
set_time_limit(0);
ini_set('max_execution_time', 9999999);
ini_set('memory_limit', '2048M');

include_once("./_common.php");
//include_once(G5_LIB_PATH."/mailer.lib.php");
include_once("./Snoopy.class.php");

####################

$member_id = $member['mb_id'];

####################

$_is_auth_ = false;
if ( $member_id == 'comsys' ) {
    $_is_auth_ = true;
} // end if.

####################

// 특수문자 변환
function specialchars_replace($str, $len=0) {
    if ($len) {
        $str = substr($str, 0, $len);
    }
    $str = str_replace(array("&", "<", ">", '"', "'"), array("&amp;", "&lt;", "&gt;", "``", "`"), $str);
    return $str;
} // end function.

####################

$t[] = date("Y-m-d H:i:s", time() );

echo '<link rel="stylesheet" href="'.G5_CSS_URL.'/'.(G5_IS_MOBILE?'mobile':'default').$shop_css.'.css?ver='.G5_CSS_VER.'">'.PHP_EOL;

####################
## 매칭 데이터 처리

$match_key = trim($match_key);
if ( $match_key ) {

    sql_query(" DELETE FROM {$_db_match} WHERE member_id='{$member_id}' ");

    $match_key_arr = explode("\n",$match_key);
    foreach ($match_key_arr as $key => $one_match_key) {

        $one_match_key = trim($one_match_key);
        if ( !$one_match_key ) continue;

        $sql = " INSERT INTO {$_db_match} SET member_id='{$member_id}' , match_key='{$one_match_key}' , rdate=now() ";
        sql_query($sql);

    } // end foreach.

    echo '<p>매칭 데이터 처리 완료</p>';

} // end if.

####################
## 키워드 처리

$keyword = trim($keyword);
if ( $keyword ) {

    sql_query(" DELETE FROM {$_db_list} WHERE member_id='{$member_id}' ");

    $keyword_arr = explode("\n",$keyword);
    foreach ($keyword_arr as $key => $one_keyword) {

        $one_keyword = trim($one_keyword);
        if ( !$one_keyword ) continue;

        $sql = " INSERT INTO {$_db_list} SET member_id='{$member_id}' , keyword='{$one_keyword}' , rdate=now() ";
        sql_query($sql);

        ob_flush();
        flush();

    } // end foreach.

} // end if.

// 혹시 몰라서 일단 분리
$searchArr = array(
    'naver'=>'https://search.naver.com/search.naver?where=nexearch&ie=utf8&query=',
    'm_naver'=>'https://m.search.naver.com/search.naver?where=nexearch&ie=utf8&query=',
    'daum'=>'http://search.daum.net/search?w=tot&q=',
    'naver_cafe'=>'https://search.naver.com/search.naver?where=article&sm=tab_jum&ie=utf8&query=',
    'naver_blog'=>'https://search.naver.com/search.naver?where=post&sm=tab_jum&ie=utf8&query=',
    'naver_post'=>'http://post.naver.com/search/post.nhn?keyword=',
);

// sch_storefarm == 1 일 경우 배열 추가
$sch_storefarm = $_POST['sch_storefarm'];
if ( $sch_storefarm == 1 ) {
    array_push($searchArr,
        array('storefarm'=>'http://shopping.naver.com/search/all.nhn?query=')
    );
} // end if.

if ( $keyword ) {

    $matchSQL = " SELECT * FROM {$_db_match} WHERE member_id='{$member_id}' ";
    $matchRES = sql_query($matchSQL);
    $matchCOUNT = sql_num_rows($matchRES);
    if ( $matchCOUNT == 0 ) alert("매칭 데이터가 없습니다.");
    $matchData = array();
    for ( $i=0; $row=sql_fetch_array($matchRES); $i++ ) {
        $matchData[] = trim($row['match_key']);
    } // end for.

    ## 기존 검색결과 삭제. 그래도 된대..
    sql_query(" DELETE FROM {$_db_data} WHERE member_id='{$member_id}' ");

    $keyword_arr = explode("\n",$keyword);
    foreach ($keyword_arr as $key => $one_keyword) {

        if ( !$one_keyword ) continue;

            foreach ( $searchArr as $target => $searchURL ) {

                $url = $searchURL.$one_keyword;

                #### NAVER
                if ( $target == 'naver' ) {
                //if ( $target == 'naver' && $_is_auth_ !== true ) {

                    $search_time = date( "YmdHis" , time() );

                    $snoopy = new Snoopy;
                    $snoopy->referer = "https://www.naver.com/";
                    $snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.2)";
                    $snoopy->rawheaders["Pragma"] = "no-cache";
                    $snoopy->cookies = "nx_ssl=2;";
                    $snoopy->fetch($url);
                    $result_source = $snoopy->results;

                    ## 분류별
                    preg_match_all("@(<div class=\"section_head\"> <h2>.*)<div class=\"section_more\">@Us",$result_source,$matchSection);
                    $section_source = array_pop($matchSection);

                    ## 각 분류별 매칭 검색
                    foreach ( $section_source as $s => $sectionSource ) { // 섹션별로 분석

                        // 분류명
                        preg_match("@<h2>(.*)</h2>@Us",$sectionSource,$matchCateName);
                        $cateName = trim(array_pop($matchCateName));

                        // 지도 검색에서만 광고랑 일반 결과랑 나누자.
                        if ( $cateName == '지도' ) {

                            $cateName = '지도'.' - '.'광고';

                            // 광고영역 추출
                            preg_match("@(<ul class=\"lst_map\"> <li id=\"sp_local_ad_1\">.*</ul>)@Us",$sectionSource,$matchADList);
                            $adList = trim(array_pop($matchADList));

                            // 분류 내 결과물 분리 - 광고
                            preg_match_all("@(<dt>.*</dt>)@Us",$adList,$matchList);
                            $list = array_pop($matchList);

                            ## 리스트 내 검색
                            $rank = 1;
                            foreach ( $list as $k => $listSource ) {

                                foreach ( $matchData as $kk => $mm ) {

                                    if ( strpos( $listSource , $mm ) ) {

                                        echo '<p>TARGET : '.$target.' / KEYWORD : '.$one_keyword.' / '.$cateName.'</p>';
                                        echo '<p>RANK : '.$rank.'<br/><xmp>'.$listSource.'</xmp></p><br/>';

                                        $listSource = specialchars_replace($listSource);
                                        $match_key = trim($mm);

                                        $sql = "
                                            INSERT INTO
                                                {$_db_data}
                                            SET
                                                member_id='{$member_id}'
                                                , engine='{$target}'
                                                , keyword='{$one_keyword}'
                                                , match_key='{$match_key}'
                                                , search_time='{$search_time}'
                                                , category='{$cateName}'
                                                , ranking='{$rank}'
                                                , code='{$listSource}'
                                                , rdate=now()
                                        ";
                                        sql_query($sql);

                                        ob_flush();
                                        flush();

                                        $stime = mt_rand(100000,1000000);
                                        usleep($stime);

                                    } // end if.

                                } // end foreach.
                                $rank++;

                            } // end foreach.

                            $cateName = '지도'.' - '.'일반';

                            // 광고영역 추출
                            preg_match("@(<ul class=\"lst_map\"> <li id=\"sp_local_1\">.*</ul>)@Us",$sectionSource,$matchADList);
                            $normalList = trim(array_pop($matchADList));

                            // 분류 내 결과물 분리 - 일반
                            preg_match_all("@(<dt>.*</dt>)@Us",$normalList,$matchList);
                            $list = array_pop($matchList);

                            ## 리스트 내 검색
                            $rank = 1;
                            foreach ( $list as $k => $listSource ) {

                                foreach ( $matchData as $kk => $mm ) {

                                    if ( strpos( $listSource , $mm ) ) {

                                        echo '<p>TARGET : '.$target.' / KEYWORD : '.$one_keyword.' / '.$cateName.'</p>';
                                        echo '<p>RANK : '.$rank.'<br/><xmp>'.$listSource.'</xmp></p><br/>';

                                        $listSource = specialchars_replace($listSource);
                                        $match_key = $mm;

                                        $sql = "
                                            INSERT INTO
                                                {$_db_data}
                                            SET
                                                member_id='{$member_id}'
                                                , engine='{$target}'
                                                , keyword='{$one_keyword}'
                                                , match_key='{$match_key}'
                                                , search_time='{$search_time}'
                                                , category='{$cateName}'
                                                , ranking='{$rank}'
                                                , code='{$listSource}'
                                                , rdate=now()
                                        ";
                                        sql_query($sql);

                                        ob_flush();
                                        flush();

                                        $stime = mt_rand(100000,1000000);
                                        usleep($stime);

                                    } // end if.

                                } // end foreach.

                                $rank++;
                            } // end foreach.

                        } else {

                            // 분류 내 결과물 분리
                            preg_match_all("@(<dt>.*</dt>)@Us",$sectionSource,$matchList);
                            $list = array_pop($matchList);

                            ## 리스트 내 검색
                            $rank = 1;
                            foreach ( $list as $k => $listSource ) {

                                foreach ( $matchData as $kk => $mm ) {

                                    if ( strpos( $listSource , $mm ) ) {

                                        echo '<p>TARGET : '.$target.' / KEYWORD : '.$one_keyword.' / '.$cateName.'</p>';
                                        echo '<p>RANK : '.$rank.'<br/><xmp>'.$listSource.'</xmp></p><br/>';

                                        $listSource = specialchars_replace($listSource);
                                        $match_key = $mm;

                                        $sql = "
                                            INSERT INTO
                                                {$_db_data}
                                            SET
                                                member_id='{$member_id}'
                                                , engine='{$target}'
                                                , keyword='{$one_keyword}'
                                                , match_key='{$match_key}'
                                                , search_time='{$search_time}'
                                                , category='{$cateName}'
                                                , ranking='{$rank}'
                                                , code='{$listSource}'
                                                , rdate=now()
                                        ";
                                        sql_query($sql);

                                        ob_flush();
                                        flush();

                                        $stime = mt_rand(100000,1000000);
                                        usleep($stime);

                                    } // end if.

                                } // end foreach.
                                $rank++;

                            } // end foreach.

                        } // end if.

                    } // end foreach.

                #### NAVER mobile
                } else if ( $target == 'm_naver' ) {
                //} else if ( $target == 'm_naver' && $_is_auth_ === true ) {

                    if ( $key > 0 ) break;

                    $search_time = date( "YmdHis" , time() );

                    $snoopy = new Snoopy;
                    $snoopy->referer = "https://www.naver.com/";
                    $snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.2)";
                    $snoopy->rawheaders["Pragma"] = "no-cache";
                    $snoopy->cookies = "nx_ssl=2;";
                    $snoopy->fetch($url);
                    $result_source = $snoopy->results;

                    //echo '<p><xmp>'.$result_source.'</xmp></p>'; exit;

                    ## 분류별
                    preg_match_all("@(<section class=\"sc.*</section>)@Us",$result_source,$matchSection);
                    $section_source = array_pop($matchSection);

                    ## 각 분류별 매칭 검색
                    foreach ( $section_source as $s => $sectionSource ) { // 섹션별로 분석

                        //echo '<p><xmp>'.$sectionSource.'</xmp></p>'; exit;

                        // 분류명
                        preg_match("@<h2 class=\"[api_title|api_title _title|api_title blind]+\">(.*)</h2@Us",$sectionSource,$matchCateName);
                        $cateName = trim(array_pop($matchCateName));
                        $cateName = preg_replace("!<a(.*?)<\/a>!is","",$cateName);
                        $cateName = preg_replace("!<i(.*?)<\/i>!is","",$cateName);

                        if ( $cateName == '이미지' || $cateName == '동영상' || $cateName == '사이트' || $cateName == '네이버쇼핑' ) continue;

                        //echo '<p><xmp>'.$cateName.'</xmp></p><br/>';
                        //echo '<p><xmp>'.$sectionSource.'</xmp></p><br/><br/>';

                        // 목록 추출
                        preg_match_all("@(<li class=\"[map_item|bx]+\">.*</li>)@Us",$sectionSource,$matchList);
                        $list = array_pop($matchList);

                        ## 리스트 내 검색
                        $rank = 1;
                        foreach ( $list as $k => $listSource ) {

                            if ( $cateName == '플레이스' || $cateName == '뉴스' ) {
                                $listSource = strip_tags($listSource);
                            } // end if.

                            //echo '<p><xmp>'.$listSource.'</xmp></p><br/><br/>';

                            foreach ( $matchData as $kk => $mm ) {

                                if ( strpos( $listSource , $mm ) ) {

                                    echo '<p>TARGET : '.$target.' / KEYWORD : '.$one_keyword.' / '.$cateName.'</p>';
                                    echo '<p>RANK : '.$rank.'<br/><xmp>'.$listSource.'</xmp></p><br/>';

                                    $listSource = specialchars_replace($listSource);
                                    $match_key = $mm;

                                    $sql = "
                                        INSERT INTO
                                            {$_db_data}
                                        SET
                                            member_id='{$member_id}'
                                            , engine='{$target}'
                                            , keyword='{$one_keyword}'
                                            , match_key='{$match_key}'
                                            , search_time='{$search_time}'
                                            , category='{$cateName}'
                                            , ranking='{$rank}'
                                            , code='{$listSource}'
                                            , rdate=now()
                                    ";
                                    //echo '<p>'.$sql.'</p>';
                                    sql_query($sql);

                                    ob_flush();
                                    flush();

                                    $stime = mt_rand(100000,1000000);
                                    usleep($stime);

                                } // end if.

                            } // end foreach.
                            $rank++;

                        } // end foreach.

                        echo '<br/><br/><br/><br/><br/><br/>';

                    } // end foreach.

                // DAUM
                } else if ( $target == 'daum' ) {
                //} else if ( $target == 'daum' && $_is_auth_ !== true ) {

                    $search_time = date( "YmdHis" , time() );

                    $snoopy = new Snoopy;
                    //$snoopy->referer = "https://www.naver.com/";
                    $snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.2)";
                    $snoopy->rawheaders["Pragma"] = "no-cache";
                    $snoopy->fetch($url);
                    $result_source = $snoopy->results;

                    ## 분류별
                    preg_match_all("@(<div disp-attr='.*' style=\".*\" class=\"g_comp\">.*)<div class=\"line\"@Us",$result_source,$matchSection);
                    $section_source = array_pop($matchSection);

                    ## 각 분류별 매칭 검색
                    foreach ( $section_source as $s => $sectionSource ) { // 섹션별로 분석

                        // 분류명
                        preg_match("@<h2 class=\"tit\">(.*)</h2@Us",$sectionSource,$matchCateName);
                        $cateName = trim(array_pop($matchCateName));
                        $cateName = preg_replace("@<span[^>]+>.*?</span>@Us","",$cateName);
                        $cateName = strip_tags($cateName);

                        //if ( $_is_auth_ === true ) echo '<p>카테고리명 : <xmp>'.$cateName.'</xmp></p><br/>';

                        if ( $cateName == '관련' || $cateName == '' ) continue;

                        // 분류 내 결과물 분리
                        preg_match_all("@(<li.*</li>)@Us",$sectionSource,$matchList);
                        $list = array_pop($matchList);

                        ## 리스트 내 검색
                        $rank = 1;
                        foreach ( $list as $k => $listSource ) {

                            foreach ( $matchData as $kk => $mm ) {

                                if ( strpos( $listSource , $mm ) ) {

                                    echo '<p>TARGET : '.$target.' / KEYWORD : '.$one_keyword.' / '.$cateName.'</p>';
                                    echo '<p>RANK : '.$rank.'<br/><xmp>'.$listSource.'</xmp></p><br/>';
                                    //if ( $_is_auth_ === true ) echo '<p>원래소스 : <xmp>'.$sectionSource.'</xmp></p><br/>';

                                    $listSource = specialchars_replace($listSource);
                                    $match_key = $mm;

                                    $sql = "
                                        INSERT INTO
                                            {$_db_data}
                                        SET
                                            member_id='{$member_id}'
                                            , engine='{$target}'
                                            , keyword='{$one_keyword}'
                                            , match_key='{$match_key}'
                                            , search_time='{$search_time}'
                                            , category='{$cateName}'
                                            , ranking='{$rank}'
                                            , code='{$listSource}'
                                            , rdate=now()
                                    ";
                                    sql_query($sql);

                                    ob_flush();
                                    flush();

                                    $stime = mt_rand(100000,1000000);
                                    usleep($stime);

                                } // end if.

                            } // end foreach.
                            $rank++;

                        } // end foreach.

                    } // end foreach.

                ## 네이버 카페 카테고리
                } else if ( $target == 'naver_cafe' ) {
                //} else if ( $target == 'naver_cafe' && $_is_auth_ !== true ) {

                    $search_time = date( "YmdHis" , time() );

                    $snoopy = new Snoopy;
                    $snoopy->referer = "https://www.naver.com/";
                    $snoopy->agent = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36";
                    $snoopy->rawheaders["Pragma"] = "no-cache";
                    $snoopy->cookies = "nx_ssl=2;";
                    $snoopy->fetch($url);
                    $result_source = $snoopy->results;

                    ## 분류별
                    preg_match_all("@(<div class=\"cafe_article.*id=\"_cafe_section\">.*)<div class=\"paging\"@Us",$result_source,$matchSection);
                    $section_source = array_pop($matchSection);

                    ## 각 분류별 매칭 검색
                    foreach ( $section_source as $s => $sectionSource ) { // 섹션별로 분석

                        // 분류명
                        preg_match("@<h2>(.*)</h2>@Us",$sectionSource,$matchCateName);
                        $cateName = trim(array_pop($matchCateName));

                        // 분류 내 결과물 분리
                        preg_match_all("@(<dt>.*</dt>)@Us",$sectionSource,$matchList);
                        $list = array_pop($matchList);

                        ## 리스트 내 검색
                        $rank = 1;
                        foreach ( $list as $k => $listSource ) {

                            foreach ( $matchData as $kk => $mm ) {

                                if ( strpos( $listSource , $mm ) ) {

                                    echo '<p>TARGET : '.$target.' / KEYWORD : '.$one_keyword.' / '.$cateName.'</p>';
                                    echo '<p>RANK : '.$rank.'<br/><xmp>'.$listSource.'</xmp></p><br/>';

                                    //if ( $_is_auth_ === true ) echo '<p><xmp>'.$result_source.'</xmp></p>';

                                    $listSource = specialchars_replace($listSource);
                                    $match_key = $mm;

                                    $sql = "
                                        INSERT INTO
                                            {$_db_data}
                                        SET
                                            member_id='{$member_id}'
                                            , engine='{$target}'
                                            , keyword='{$one_keyword}'
                                            , match_key='{$match_key}'
                                            , search_time='{$search_time}'
                                            , category='{$cateName}'
                                            , ranking='{$rank}'
                                            , code='{$listSource}'
                                            , rdate=now()
                                    ";
                                    sql_query($sql);

                                    ob_flush();
                                    flush();

                                    $stime = mt_rand(100000,1000000);
                                    usleep($stime);

                                } // end if.

                            } // end foreach.
                            $rank++;

                        } // end foreach.

                    } // end foreach.

                ## 네이버 블로그 카테고리
                } else if ( $target == 'naver_blog' ) {
                //} else if ( $target == 'naver_blog' && $_is_auth_ !== true ) {

                    $search_time = date( "YmdHis" , time() );

                    $snoopy = new Snoopy;
                    $snoopy->referer = "https://www.naver.com/";
                    $snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.2)";
                    $snoopy->rawheaders["Pragma"] = "no-cache";
                    $snoopy->cookies = "nx_ssl=2;";
                    $snoopy->fetch($url);
                    $result_source = $snoopy->results;

                    ## 분류별
                    preg_match_all("@(<div class=\"blog section.*)<div class=\"paging\"@Us",$result_source,$matchSection);
                    $section_source = array_pop($matchSection);

                    ## 각 분류별 매칭 검색
                    foreach ( $section_source as $s => $sectionSource ) { // 섹션별로 분석

                        // 분류명
                        preg_match("@<h2>(.*)</h2>@Us",$sectionSource,$matchCateName);
                        $cateName = trim(array_pop($matchCateName));

                        // 분류 내 결과물 분리
                        preg_match_all("@(<dt>.*</dt>)@Us",$sectionSource,$matchList);
                        $list = array_pop($matchList);

                        ## 리스트 내 검색
                        $rank = 1;
                        foreach ( $list as $k => $listSource ) {

                            foreach ( $matchData as $kk => $mm ) {

                                if ( strpos( $listSource , $mm ) ) {

                                    echo '<p>TARGET : '.$target.' / KEYWORD : '.$one_keyword.' / '.$cateName.'</p>';
                                    echo '<p>RANK : '.$rank.'<br/><xmp>'.$listSource.'</xmp></p><br/>';

                                    $listSource = specialchars_replace($listSource);
                                    $match_key = $mm;

                                    $sql = "
                                        INSERT INTO
                                            {$_db_data}
                                        SET
                                            member_id='{$member_id}'
                                            , engine='{$target}'
                                            , keyword='{$one_keyword}'
                                            , match_key='{$match_key}'
                                            , search_time='{$search_time}'
                                            , category='{$cateName}'
                                            , ranking='{$rank}'
                                            , code='{$listSource}'
                                            , rdate=now()
                                    ";
                                    sql_query($sql);

                                    ob_flush();
                                    flush();

                                    $stime = mt_rand(100000,1000000);
                                    usleep($stime);

                                } // end if.

                            } // end foreach.
                            $rank++;

                        } // end foreach.

                    } // end foreach.

                ## 네이버 포스트 카테고리
                } else if ( $target == 'naver_post' ) {
                //} else if ( $target == 'naver_post' && $_is_auth_ !== true ) {

                    $search_time = date( "YmdHis" , time() );

                    $snoopy = new Snoopy;
                    $snoopy->referer = "https://www.naver.com/";
                    $snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.2)";
                    $snoopy->rawheaders["Pragma"] = "no-cache";
                    $snoopy->cookies = "nx_ssl=2;";
                    $snoopy->fetch($url);
                    $result_source = $snoopy->results;

                    ## 분류별
                    //preg_match_all("@(<div id=\"cont\".*role=\"main\">.*)<div id=\"more_btn@Us",$result_source,$matchSection);
                    preg_match_all("@(<div id=\"_list_container\" class=\"lst_feed_wrap\">.*)<div id=\"more_btn@Us",$result_source,$matchSection);
                    $section_source = array_pop($matchSection);

                    ## 각 분류별 매칭 검색
                    foreach ( $section_source as $s => $sectionSource ) { // 섹션별로 분석

                        // 분류명
                        preg_match("@<legend>(.*)</legend>@Us",$sectionSource,$matchCateName);
                        $cateName = trim(array_pop($matchCateName));
                        if ( !$cateName ) $cateName = '네이버 포스트';

                        // 분류 내 결과물 분리
                        preg_match_all("@(<li.*</li>)@Us",$sectionSource,$matchList);
                        $list = array_pop($matchList);

                        ## 리스트 내 검색
                        $rank = 1;
                        foreach ( $list as $k => $listSource ) {

                            foreach ( $matchData as $kk => $mm ) {

                                if ( strpos( $listSource , $mm ) ) {

                                    echo '<p>TARGET : '.$target.' / KEYWORD : '.$one_keyword.' / '.$cateName.'</p>';
                                    echo '<p>RANK : '.$rank.'<br/><xmp>'.$listSource.'</xmp></p><br/>';

                                    $listSource = specialchars_replace($listSource);
                                    $match_key = $mm;

                                    $sql = "
                                        INSERT INTO
                                            {$_db_data}
                                        SET
                                            member_id='{$member_id}'
                                            , engine='{$target}'
                                            , keyword='{$one_keyword}'
                                            , match_key='{$match_key}'
                                            , search_time='{$search_time}'
                                            , category='{$cateName}'
                                            , ranking='{$rank}'
                                            , code='{$listSource}'
                                            , rdate=now()
                                    ";
                                    sql_query($sql);

                                    ob_flush();
                                    flush();

                                    $stime = mt_rand(100000,1000000);
                                    usleep($stime);

                                } // end if.

                            } // end foreach.
                            $rank++;

                        } // end foreach.

                    } // end foreach.
                    // end.

                } // end if.

            } // end foreach.

        ob_flush();
        flush();

        $stime = mt_rand(100000,1000000);
        usleep($stime);

    } // end foreach.

    echo '<p><h2 style="font-size: 20px;">Message : OK.</h2></p>';

} // end if.

##########################################

exit;
