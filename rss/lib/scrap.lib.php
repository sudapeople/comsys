<?php

function action_storefarm( $scrapData , $user_id , $target ) {
    global $__TABLE__list , $__TABLE__data , $__TABLE__target ;

    $urlBase = 'http://storefarm.naver.com';

    // 페이지명 또는 스토어 상점명 등 title 수집
    preg_match("@<div class=\"inner\">\s+<h1>(.*)</h1>@Us",$scrapData,$matchTitle);
    $titleBin = array_pop($matchTitle);
    $title = strip_tags($titleBin);

    // 페이지 대표이미지
    // <meta property="og:image" content="http://shop2.phinf.naver.net/20170105_288/gbseller808_1483600424104LHcR6_PNG/7865256369335848_1657310666.png">
    preg_match("@<meta property=\"og:image\" content=\"([^\"]+)\">@Us",$scrapData,$matchMainImg);
    //print_r( $matchMainImg ); exit;
    $mainImage = array_pop($matchMainImg);
    //echo $mainImage; exit;

    sql_query(" UPDATE {$__TABLE__list} SET title='".$title."' , mainImage='".$mainImage."' WHERE user_id='".$user_id."' AND target='".$target."' ");

    //preg_match("@(<div class=\"lst_align\">.*<div id=\"footer)@Us",$scrapData,$matchCutList); // 이것도 잘 됨. <li> 개수가 안 맞아서..
    preg_match("@(<div class=\"lst_align\">.*<\/form>)@Us",$scrapData,$matchCutList);
    $scrapDataCutProdList = array_pop($matchCutList);

    //echo( '<xmp>'.$scrapDataCutProdList.'</xmp>' );

    preg_match_all("@(<li>.*</li>)@Us",$scrapDataCutProdList,$matchCutProd);
    $scrapDataCutProd = array_pop($matchCutProd);

    //print_r( $scrapDataCutProd );

        # 상품 정보
        foreach ( $scrapDataCutProd as $key => $prdSource ) {

            // target_url 링크주소
            preg_match("@href=\"([^\"]+)\"@Us",$prdSource,$matchPrdUrl);
            $prdURL = array_pop($matchPrdUrl);
            $prdURL = preg_replace("/^\//","",$prdURL);
            $target_url = $urlBase.'/'.$prdURL;

            // <img src=\"\"
            // target_img 컨텐츠별 대표이미지
            preg_match("@<img src=\"([^\"]+)\"@Us",$prdSource,$matchPrdImg);
            $prdImg = array_pop($matchPrdImg);
            $prdImg = str_replace("?type=m120","",$prdImg);
            $target_img = $prdImg;

            // target_subject 상품명
            preg_match("@alt=\"([^\"]+)\"@Us",$prdSource,$matchPrdSubject);
            $prdSubject = array_pop($matchPrdSubject);
            $target_subject = $prdSubject;

            // check soldout 품절
            preg_match("@(class=\"soldout\")@Us",$prdSource,$matchPrdSoldout);
            $prdSoldout = array_pop($matchPrdSoldout);
            $target_soldout = $prdSoldout;

            //if ( !$target_soldout ) echo "<p>Prod Info : ".$target_url." / ".$target_subject." / ".$target_img." / ".$target_soldout."</p>";
            //echo "<p>Prod Info : ".$target_url." / ".$target_subject." / ".$target_soldout."</p>";
            if ( !$target_soldout ) {

                // check 중복
                $sqlCheck = " SELECT * FROM {$__TABLE__data} WHERE target_url='".$target_url."' ";
                $resCheck = sql_query($sqlCheck);
                $resRows = @sql_num_rows($resCheck);

                if ( $resRows > 0 ) { // 중복
                    $sql = " UPDATE {$__TABLE__data} SET target='".$target."' , target_subject='".$target_subject."' , target_img='".$target_img."' , udate=now() WHERE target_url='".$target_url."' ";
                } else { // 신규
                    $sql = " INSERT INTO {$__TABLE__data} SET user_id='".$user_id."' , target='".$target."' , target_url='".$target_url."' , target_subject='".$target_subject."' , target_img='".$target_img."' , rdate=now() ";
                } // end if.

                sql_query($sql);

            } // end if.

        } // end foreach.

} // end function.

///////////////////////////////// NAVER BLOG

function action_naverblog( $scrapData , $user_id , $target ) {
    global $__TABLE__list , $__TABLE__data , $__TABLE__target ;

    // 페이지명 또는 스토어 상점명 등 title 수집
    preg_match("@<meta property=\"og:title\" content=\"([^\"]+)\"/>@Us",$scrapData,$matchTitle);
    $titleBin = array_pop($matchTitle);
    $title = strip_tags($titleBin);

    // 페이지 대표이미지
    preg_match("@<meta property=\"og:image\" content=\"([^\"]+)\"@Us",$scrapData,$matchMainImg);
    $mainImage = array_pop($matchMainImg);
    $mainImage = str_replace('?type=f204_204','',$mainImage);

    sql_query(" UPDATE {$__TABLE__list} SET title='".$title."' , mainImage='".$mainImage."' WHERE user_id='".$user_id."' AND target='".$target."' ");

    // RSS 페이지를 수집 /////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $naverBlogRssPageURL = 'http://blog.rss.naver.com/'.$user_id.'.xml';
    $scrapRssPageData = scrap($naverBlogRssPageURL);

    preg_match_all("@(<item>.*</item>)@Us",$scrapRssPageData,$matchCutList);
    $scrapDataCutProdList = array_pop($matchCutList);

    # 상품 정보
    foreach ( $scrapDataCutProdList as $key => $prdSource ) {

        if ( $key > 4 ) break;

        // target_url 링크주소
        preg_match("@<link>(.*)</link>@Us",$prdSource,$matchPrdUrl);
        $prdURL = array_pop($matchPrdUrl);
        $target_url = trim(strip_tags($prdURL));
        $target_url_slashed = explode("/", $target_url);
        $target_url_end = array_pop($target_url_slashed);
        $blogOriginalLink = 'http://blog.naver.com/PostView.nhn?blogId='.$user_id.'&logNo='.$target_url_end.'&redirect=Dlog&widgetTypeCall=true';

        // target_subject 상품명
        preg_match("@<title>(.*)</title>@Us",$prdSource,$matchPrdSubject);
        $prdSubject = array_pop($matchPrdSubject);
        $prdSubjectCDATA = preg_match("@<\!\[CDATA\[(.*)\]\]>@Us",$prdSubject,$matchStripPrdSubject);
        $prdSubjectPOP = array_pop($matchStripPrdSubject);
        $target_subject = $prdSubjectPOP;
        if ( !$target_subject ) $target_subject = $prdSubject;

        // Scrap description.
        $scrapDescData = scrap($blogOriginalLink);

        preg_match("@(<div class=\"se_doc_contents_start\".*)<div class=\"post_footer_contents\">@Us",$scrapDescData,$matchDesc);
        $matchDescSource = preg_replace("/\n+/","<br />",str_replace("  ","",str_replace("\t","",trim(strip_tags(iconv('euc-kr', 'utf-8', array_pop($matchDesc)))))));

        $target_desc = str_replace("&amp;","",$matchDescSource);
        $target_desc = str_replace("&gt;","",$matchDescSource);
        $target_desc = str_replace("&lt;","",$matchDescSource);
        $target_desc = str_replace("&nbsp;"," ",$matchDescSource);
        $target_desc = str_replace("&amp;amp;nbsp;","",$matchDescSource);
        $target_desc = str_replace("&amp;amp;gt;","",$matchDescSource);
        $target_desc = specialchars_replace($target_desc);

        // target_img 컨텐츠별 대표이미지
        $target_img = $mainImage;

        // check 중복
        $sqlCheck = " SELECT * FROM {$__TABLE__data} WHERE target_url='".$target_url."' ";
        $resCheck = sql_query($sqlCheck);
        $resRows = @sql_num_rows($resCheck);

        if ( $resRows > 0 ) { // 중복
            $sql = " UPDATE {$__TABLE__data} SET target='".$target."' , target_subject='".$target_subject."' , target_desc='".$target_desc."' , udate=now() WHERE target_url='".$target_url."' ";
        } else { // 신규
            $sql = " INSERT INTO {$__TABLE__data} SET user_id='".$user_id."' , target='".$target."' , target_url='".$target_url."' , target_subject='".$target_subject."' , target_desc='".$target_desc."' , target_img='".$target_img."' , rdate=now() ";
        } // end if.

        sql_query($sql);

    } // end foreach.

} // end function.

function action_naverpost( $scrapData , $user_id , $target ) {
    global $__TABLE__list , $__TABLE__data , $__TABLE__target ;

    // 페이지명 또는 스토어 상점명 등 title 수집
    preg_match("@<meta property=\"og:title\" content=\"([^\"]+)\"/>@Us",$scrapData,$matchTitle);
    $titleBin = array_pop($matchTitle);
    $title = strip_tags($titleBin);

    // 페이지 대표이미지
    preg_match("@<meta property=\"og:image\" content=\"([^\"]+)\"@Us",$scrapData,$matchMainImg);
    $mainImage = array_pop($matchMainImg);
    $mainImage = str_replace('?type=f204_204','',$mainImage);

    sql_query(" UPDATE {$__TABLE__list} SET title='".$title."' , mainImage='".$mainImage."' WHERE user_id='".$user_id."' AND target='".$target."' ");

} // end function.

function action_tistory( $scrapData , $user_id , $target ) {
    global $__TABLE__list , $__TABLE__data , $__TABLE__target ;

    // 페이지명 또는 스토어 상점명 등 title 수집
    preg_match("@<meta property=\"og:site_name\" content=\"([^\"]+)\"@Us",$scrapData,$matchTitle);
    $titleBin = trim(array_pop($matchTitle));
    $title = strip_tags($titleBin);

    // 페이지 대표이미지
    preg_match("@<meta property=\"og:image\" content=\"([^\"]+)\"@Us",$scrapData,$matchMainImg);
    $mainImage = array_pop($matchMainImg);

    sql_query(" UPDATE {$__TABLE__list} SET title='".$title."' , mainImage='".$mainImage."' WHERE user_id='".$user_id."' AND target='".$target."' ");

    // 컨텐츠 링크 수집
    preg_match_all("@(<div class=\"list_content\">.*</div>)@Us",$scrapData,$matchPostList);
    $postListArr = array_pop($matchPostList);

    foreach ( $postListArr as $key => $list ) {

        if ( $key > 4 ) break;

        // 링크주소 바로 뽑아서 스크랩 하고 처리.
        preg_match("@href=\"([^\"]+)\"@Us",$list,$matchPostLink);
        $postLink = array_pop($matchPostLink);

        $target_url = 'http://'.$user_id.'.tistory.com'.$postLink; // URL
        $postMain = scrap($target_url); // 포스팅 글 수집

        // title
        preg_match("@<h3[^>]+>(.*)</h3>@Us",$postMain,$matchTitle);
        $target_subject = trim(array_pop($matchTitle));

        // description
        preg_match("@<div class=\"area_view\">(.*)<div class=\"tt-sns-wrap\"@Us",$postMain,$matchDesc);
        $target_desc = array_pop($matchDesc);
        if ( !$target_desc ) {
            preg_match("@<div class=\"area_view\">(.*)<div style=\"text-align:left; padding-top:10px;clear:both\">@Us",$postMain,$matchDesc);
            $target_desc = array_pop($matchDesc);
        } // end if.

        // 이미지 EXIF 정보와 기타 정보 제거
        $target_desc = preg_replace("@<p style=\"text-align: left; clear: none; float: none;\"><span class=\"imageblock\".*</span></p>@Us","",$target_desc);
        // 남은 찌꺼기 제거 - 이미지 관련
        $target_desc = preg_replace("@<span class=\"imageblock\".*</span>@Us","",$target_desc);
        // <br/> => /n 으로 치환
        $target_desc = preg_replace("@<br />@Us","\n",$target_desc);
        // p 태그 제거
        $target_desc = preg_replace("@</p><p[^>]+>@Us","",$target_desc);
        $target_desc = preg_replace("@</p><p>@Us","",$target_desc);
        // b?
        $target_desc = str_replace("</b><b>","",$target_desc);

        $target_desc = str_replace("'","`",$target_desc);
        $target_desc = str_replace("&nbsp;"," ",$target_desc);
        $target_desc = str_replace("&amp;","",$target_desc);
        $target_desc = str_replace("&gt;","",$target_desc);
        $target_desc = str_replace("&lt;","",$target_desc);

        // 최종 태그 삭제
        $target_desc = trim(strip_tags($target_desc));
        $target_desc = specialchars_replace($target_desc);

        // main image
        preg_match("@<meta property=\"og:image\" content=\"([^\"]+)\"@Us",$postMain,$matchImage);
        $target_img = trim(array_pop($matchImage));

        // check 중복
        $sqlCheck = " SELECT * FROM {$__TABLE__data} WHERE target_url='".$target_url."' ";
        $resCheck = sql_query($sqlCheck);
        $resRows = @sql_num_rows($resCheck);

        if ( $resRows > 0 ) { // 중복
            $sql = " UPDATE {$__TABLE__data}
                        SET target='".$target."' ,
                            target_subject='".$target_subject."' ,
                            target_desc='".$target_desc."' ,
                            udate=now()
                        WHERE
                            target_url='".$target_url."' ";
        } else { // 신규
            $sql = " INSERT INTO {$__TABLE__data}
                        SET user_id='".$user_id."' ,
                            target='".$target."' ,
                            target_url='".$target_url."' ,
                            target_subject='".$target_subject."' ,
                            target_desc='".$target_desc."' ,
                            target_img='".$target_img."' ,
                            rdate=now() ";
        } // end if.

        //echo $sql; exit;

        sql_query($sql);

    } // end foreach.

} // end function.
