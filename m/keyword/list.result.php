<?php
include_once("./_common.php");

if ( !$member['mb_id'] ) alert("잘못된 접근입니다.");

####################

$member_id = $member['mb_id'];

####################

$_is_auth_ = false;
if ( $member_id == 'comsys' ) {
    $_is_auth_ = true;
} // end if.

####################

$query_order_array = array();
$query_order_array[] = ' engine ASC ';
$query_order_array[] = ' ranking ASC ';
$query_order_array[] = ' category ASC ';
$query_order_array[] = ' keyword ASC ';
$query_order_sum = implode(" , ",$query_order_array);
$query_order = ' ORDER BY '.$query_order_sum;

## 원하는 순서대로
$query_sort_array = array();
$query_sort_array[] = " 'naver' ";
$query_sort_array[] = " 'm_naver' ";
$query_sort_array[] = " 'naver_blog' ";
$query_sort_array[] = " 'naver_cafe' ";
$query_sort_array[] = " 'naver_post' ";
$query_sort_array[] = " 'daum' ";
$query_sort_sum = implode(" , ",$query_sort_array);

$query_sort = " ORDER BY FIELD(engine, {$query_sort_sum} ) , {$query_order_sum} ";

$sqlLIST = " SELECT * FROM {$_db_data} WHERE member_id='{$member_id}' {$query_sort} ";
$resLIST = sql_query($sqlLIST);
$resNUM = sql_num_rows($resLIST);

if ( $resNUM == 0 ) {
    if ( $_is_auth_ !== true
        //|| $_SERVER['REMOTE_ADDR'] == '220.94.192.154'
    ) {
        alert("데이터가 없습니다.");
    } // end if.
} // end if.

$searchArr = array(
    'naver'=>'https://search.naver.com/search.naver?where=nexearch&ie=utf8&query=',
    'm_naver'=>'https://m.search.naver.com/search.naver?where=nexearch&ie=utf8&query=',
    'daum'=>'http://search.daum.net/search?w=tot&q=',
    'naver_cafe'=>'https://search.naver.com/search.naver?where=article&sm=tab_jum&ie=utf8&query=',
    'naver_blog'=>'https://search.naver.com/search.naver?where=post&sm=tab_jum&ie=utf8&query=',
    'naver_post'=>'http://post.naver.com/search/post.nhn?keyword=',
);

$g5['title'] = '키워드 랭킹 검색 - 리스트';
include_once("../../_head.php");

if ( $_is_auth_ === true
    //|| $_SERVER['REMOTE_ADDR'] == '220.94.192.154'
        )
            //echo '<p>'.$sqlLIST.'</p>';

?>

<style>
	.pdt10 {padding-top:10px;}
    .pdt30 {padding-top:30px;}
	.pdt50 {padding-top:50px;}
    .pdt100 {padding-top:100px;}

    .pd10 {padding: 10px 10px;}

	.center {text-align: center;}
	.bd {font-weight: bold;}

	.number {font-size: 14px; color: #ff168f;}

    .rank_1 { background: #f1f8ff;}
    .rank_1_bd { font-weight: bold; color: #d70e0e; font-size: 14px; }

</style>

<?php
if ( $_is_super_view_ === true ) {
    $txt = "<dt><a href=''http://cafe.naver.com/hktailor3111/6636'' target=''_blank'' class=''sh_cafe_title'' title=''평촌맞춤정장 여름데님자켓 맞춤으로 캐주얼하게 코디해보세요(안양산본안산범계)'' onclick=''return goOtherCR(this, 'a=art*a.tit&r=1&i=90000004_01738CAA000019EC00000000&u='+urlencode(urlexpand(this.href)));''>평촌<strong class=''hl''>맞춤정장</strong> 여름데님자켓 <strong class=''hl''>맞춤</strong>으로 캐주얼하게 코디해보세요(안...</a></dt>";
    $match = "HK테일러 안양평촌점";
    $v = strpos($txt , $match);
} // end if.
?>


<div id="section">
    <p>2017-06-07 네이버 카페 랭킹에 오류 발견(네이버 문제) - 네이버 카페 검색결과는 검증 필요.</p>
</div>

<p class="pdt10"></p>

<section id="list">
	<table cellspacing="0" cellpadding="0">
	<tr style="height:40px; background:#f0f0f0;">
		<th style="width: 40px;">NO</th>
		<th style="width: 100px;">검색엔진</th>
		<th style="width: 100px;">키워드</th>
        <th style="width: 160px;">매칭데이터</th>
        <th style="width: 100px;">검색분류</th>
        <th style="width: 100px;">랭킹</th>
		<th style="width: 100px;">검색일시</th>
	</tr>
    <tr><td colspan="100" style="border-top:1px solid #999;"></td></tr>
	<?php
	$num = 1;
	for ( $i=0; $row=sql_fetch_array($resLIST); $i++ ) {

        $kid = trim($row['kid']);
        $member_id = trim($row['member_id']);
        $engine = trim($row['engine']);
            $view_engine = '';
            switch ( $engine ) {
                case 'naver' :
                    $view_engine = '네이버';
                    break;
                case 'm_naver' :
                    $view_engine = '(모바일)네이버';
                    break;
                case 'daum' :
                    $view_engine = '다음';
                    break;
                case 'naver_cafe' :
                    $view_engine = '네이버 카페';
                    break;
                case 'naver_blog' :
                    $view_engine = '네이버 블로그';
                    break;
                case 'naver_post' :
                    $view_engine = '네이버 포스트';
                    break;
                default :
                    $view_engine = $engine;
                    break;
            } // end switch.
        $keyword = trim($row['keyword']);
        $match_key = trim($row['match_key']);
        $search_time = trim($row['search_time']);
        $category = trim($row['category']);
            //$category = iconv("utf-8","cp949",$category);
        $ranking = trim($row['ranking']);
        $code = trim($row['code']);
            //$code = un_specialchars_replace($code);
            //$code = iconv("utf-8","cp949",$code);
        $rdate = $row['rdate'];
            $rdate1 = substr($rdate,0,10);
            $rdate2 = substr($rdate,-8);

        $_css_rank_top_ = '';
        $_css_rank_top_bd_ = '';
        if ( $ranking == 1 ) {
            $_css_rank_top_ = ' rank_1 ';
            $_css_rank_top_bd_ = ' rank_1_bd ';
        } // end if.

	?>
	<tr style="height:40px;" class="<?php echo $_css_rank_top_; ?>">
		<td class="center"><?php echo $num; ?></td>
		<td class="center"><?php echo $view_engine; ?></td>
        <td class="center"><a href="<?php echo $searchArr[$engine].$keyword ; ?>" target="_blank"><?php echo $keyword; ?></a></td>
        <td class="center"><?php echo $match_key; ?></td>
        <td class="center"><?php echo $category; ?></td>
		<td class="center<?php echo $_css_rank_top_bd_; ?>"><?php echo $ranking; ?></td>
		<td class="center"><?php echo $rdate1.'<br/>'.$rdate2; ?></td>
	</tr>
	<tr><td colspan="100" style="border-top:1px dashed #999;"></td></tr>
	<?php
	$num++;
	} // end for.
	?>
	</table>
</section>

<p class="pdt100"></p>

<?php
include_once("../../_tail.php");
