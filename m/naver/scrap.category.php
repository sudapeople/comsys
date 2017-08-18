<?php
include_once("./_common.php");
include_once(G5_LIB_PATH."/mailer.lib.php");

if ( !$cafe_id ) alert("잘못된 접근입니다.");
if ( !$member['mb_id'] ) alert("잘못된 접근입니다.");

$view_cafe_id = 'http://cafe.naver.com/'.$cafe_id;

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
    $str = str_replace(array("?", '&', "<", ">", '"', "'"), array("&#63;", "&amp;", "&lt;", "&gt;", "``", "`"), $str);
    return $str;
} // end function.

$ori_source = iconv("cp949","utf-8",scrap($view_cafe_id));

//echo '<xmp>'.$ori_source.'</xmp>';

######################################

//echo '<p>'.$manager_id.'</p>';

$chkSQL = " SELECT * FROM {$_db_navercafe_except_list} WHERE member_id='{$member_id}' AND cafe_id='{$cafe_id}' AND manager_id='{$manager_id}' ";
$chkRES = sql_query($chkSQL);
$chkNUM = sql_num_rows($chkRES);

if ( $chkNUM > 0 ) { // 중복
	$chkROW = sql_fetch($chkSQL);
	$manager_id = $chkROW['manager_id'];
} else { // 신규
	// 매니저 아이디
	//preg_match("@<div class=\"ia-info-data\" id=\"ia-info-data\">.*href=\"/".$cafe_id."/member/(.*)/@Us",$ori_source,$matchMANAGER);
	preg_match("@href=\"/".$cafe_id."/member/(.*)/@Us",$ori_source,$matchMANAGER);
	$manager_id = trim(array_pop($matchMANAGER));

	if ( $is_admin == 'super' ) {
		$sql = " INSERT INTO {$_db_navercafe_except_list} SET member_id='{$member_id}' , cafe_id='{$cafe_id}' , manager_id='{$manager_id}' , rdate=now() ";
	} else {
		$sql = " INSERT INTO {$_db_navercafe_except_list} SET member_id='".$member['mb_id']."' , cafe_id='{$cafe_id}' , manager_id='{$manager_id}' , rdate=now() ";
	}
	sql_query($sql);
} // end if.

//echo '<p>'.$manager_id.'</p>';

######################################

preg_match_all("@(<ul class=\"cafe-menu-list\".*</ul>)@Us",$ori_source,$matchLIST);
$matchLISTarr = array_pop($matchLIST);
$sumMatchLIST = implode("",$matchLISTarr);
//$list_source = iconv("cp949","utf-8",$sumMatchLIST);
$list_source = $sumMatchLIST;

//echo '<xmp>'.iconv("cp949","utf-8",$sumMatchLIST).'</xmp>';

preg_match_all("@(<li>.*</li>)@Us",$list_source,$matchLI);
$li = array_pop($matchLI);

$data = array();
foreach ( $li as $k => $view ) {
    //echo '<p>'.$k.' : <xmp>'.$view.'</xmp></p>';
    preg_match("@<a.*>(.*)</a>@Us",$view,$matchTITLE);
    $title = array_pop($matchTITLE);

    preg_match("@href=\"([^\"]+)\"@Us",$view,$matchLINK);
    $link = array_pop($matchLINK);

    //echo '<p>title : '.$title.' / link : '.$link.'</p>';
    $data[$title] = $link;
} // end foreach.

###############

// 추출 등록 아이디 - 스케쥴
if ( $is_admin == 'super' ) {
	$scSQL = " SELECT * FROM {$_db_navercafe_scrap_schedule} WHERE cafe_id='{$cafe_id}' ";
} else {
	$scSQL = " SELECT * FROM {$_db_navercafe_scrap_schedule} WHERE member_id='".$member['mb_id']."' AND cafe_id='{$cafe_id}' ";
} // end if.

$scRES = sql_query($scSQL);
$scNUM = sql_num_rows($scRES);

$chkSchedule = array();
if ( $scNUM > 0 ) {
	for ( $i=0; $row=sql_fetch_array($scRES); $i++ ) {
		$cid = $row['cid'];
		$target_url = $row['target_url'];
		$is_scrap = $row['is_scrap'];
		$chkSchedule[$target_url]['cid'] = $cid;
		$chkSchedule[$target_url]['state'] = $is_scrap;
	} // end for.
} // end if.

###############

$_is_manager = false;
if ( $manager_id ) {
	$_is_manager = true;
} // end if.

###############

$g5['title'] = '카테고리 추출';
include_once("../../_head.php");
?>

<style>

.pdt10 {padding-top:10px;}
.pdt30 {padding-top:30px;}
.pdt50 {padding-top:50px;}

.center {text-align: center;}

.pd10 {padding: 10px;}
.pd20 {padding: 20px;}
.pd30 {padding: 30px;}

.bdT1s { border-top: 1px solid #999; }
.bdB1s { border-bottom: 1px solid #999; }

.bdT1d { border-top: 1px dashed #999; }
.bdB1d { border-bottom: 1px dashed #999; }

.tbTHbg { background: #f0f0f0; height: 40px;}
.tbTDhi40 { height: 40px;}

.bd { font-weight: bold; }

</style>

<?php if ( $_is_manager === false ) { ?>
<p class="pdt10"></p>
	<div>매니저 ID 등록 실패</div>
	<div>네이버 접근제한 일 수 있으니, 10분 또는 30분, 정각까지 기다렸다가 다시 시도해 보세요.</div>
<p class="pdt10"></p>
<?php } else { ?>
<p class="pdt10"></p>
	<table cellpadding="0" cellspacing="0">
	<tr><td colspan="100" class="bdT1s"></td></tr>
	<tr class="tbTHbg">
		<th class="center" style="width:30px;">NO</th>
		<th class="center" style="width:100px;">카페ID</th>
		<th class="center" style="width:460px;">카페명</th>
		<th class="center" style="width:100px;">매니저 아이디</th>
	</tr>
	<tr><td colspan="100" class="bdB1s"></td></tr>
	<?php
	if ( $is_admin == 'super' ) {
		$sqlM = " SELECT * FROM {$_db_navercafe_except_list} WHERE member_id='{$member_id}' AND cafe_id='{$cafe_id}' ";
	} else {
		$sqlM = " SELECT * FROM {$_db_navercafe_except_list} WHERE member_id='".$member['mb_id']."' AND cafe_id='{$cafe_id}' ";
	} // end if.

	//echo '<p>'.$sqlM.'</p>';

	$resM = sql_query($sqlM);
	$num = 1;
	for ( $m=0; $rowM=sql_fetch_array($resM); $m++ ) {
		$member_id = $rowM['member_id'];
		$cafe_id = $rowM['cafe_id'];
		$manager_id = $rowM['manager_id'];
			$schRES = sql_fetch(" SELECT * FROM {$_db_navercafe_list} WHERE member_id='{$member_id}' AND cafe_id='{$cafe_id}' ");
			$cafe_title = $schRES['cafe_title'];
	?>
	<tr class="tbTDhi40">
		<td class="center"><?php echo $num; ?></td>
		<td class="center"><?php echo $cafe_id; ?></td>
		<td class="center"><?php echo $cafe_title; ?></td>
		<td class="center bd"><?php echo $manager_id; ?></td>
	</tr>
	<tr><td colspan="100" class="bdT1d"></td></tr>
	<?php
	//$chk_manager_id = $manager_id;
	$num++;
	} // end for.
	?>
	</table>

<p class="pdt30"></p>

<?php } // end if. ?>

<table cellspacing="0" style="width:100%;">
<tr style="background: #f0f0f0; padding: 10px 0px;">
	<th style="width:30px; height:40px;">NO</th>
	<th style="width:160px;">카테고리명</th>
	<th>URL</th>
	<th style="width:90px;">아이디 추출</th>
</tr>
<?php
$num = 1;
foreach ( $data as $cate_title => $link ) {
$view_link = $view_cafe_id.$link.'&search.page=1&userDisplay=50';

//$mod_view_link = str_replace(array("?","&",),array("^","#",),$view_link);

if ( preg_match("@^http@Us",$link) ) continue;
if ( !preg_match("@^/ArticleList\.nhn@Us",$link) ) continue;

$disabled = '';
$is_state = '';
$unScrap = '';
$selected_style = '';

if ( $chkSchedule[$link]['state'] ) {
	$disabled = ' disabled="disabled" ';
	$unScrap = '
	<input type="hidden" name="unscrap" value="unscrap" />
	<input type="hidden" name="cid" value="'.$chkSchedule[$link]['cid'].'" />
	<input type="submit" value="아이디 추출 해제" style="padding: 5px 5px;" />
	';
	$selected_style = 'background: #fff3b4;';
} // end if.

if ( $chkSchedule[$link]['state'] == 'waiting' ) {
	$is_state = '<p>waiting</p>';
} else if ( $chkSchedule[$link]['state'] == 'scraping' ) {
	$is_state = '<p>scraping</p>';
} else if ( $chkSchedule[$link]['state'] == 'completed' ) {
	$is_state = '<p>completed</p>';
} // end if.
?>
<tr style="height:60px; <?php echo $selected_style; ?>">
	<td class="center"><?php echo $num; ?></td>
	<td><?php echo $cate_title; ?></td>
	<td><a href="<?php echo $view_link; ?>" target="_blank"><?php echo $link; ?></a></td>
	<td class="center">
		<!-- <button type="button" style="padding: 5px 5px;" onclick="window.open('/m/naver/scrap.id.php?cafe_id=<?php echo $cafe_id; ?>&url=<?php echo $view_link; ?>')">아이디 추출</button> -->
		<form method="post" action="scrap.id.php">
			<input type="hidden" name="cafe_id" value="<?php echo $cafe_id; ?>" />
			<input type="hidden" name="url" value="<?php echo $view_link; ?>" />
			<input type="submit" value="아이디 추출 샘플" style="padding: 5px 5px;" />
		</form>
		<p style="padding-top: 4px;"></p>
		<form method="post" action="scrap.id.write.php">
			<input type="hidden" name="cafe_id" value="<?php echo $cafe_id; ?>" />
			<input type="hidden" name="cafe_title" value="<?php echo $cafe_title; ?>" />
			<input type="hidden" name="cate_title" value="<?php echo $cate_title; ?>" />
			<?php if ( $is_admin == 'super' ) { ?>
			<input type="hidden" name="member_id" value="<?php echo $member_id; ?>" />
			<?php } else { ?>
			<input type="hidden" name="member_id" value="<?php echo $member['mb_id']; ?>" />
			<?php } // end if. ?>
			<input type="hidden" name="target_url" value="<?php echo $link; ?>" />
			<input type="submit" value="아이디 추출 등록" style="padding: 5px 5px;" <?php echo $disabled; ?> />
			<?php echo $is_state; ?>
			<?php echo $unScrap; ?>
		</form>
	</td>
</tr>
<tr><td colspan="100" style="border-top: 1px dashed #999;"></td></tr>
<?php
$num++;
} // end foreach.
?>
</table>

<p class="pdt50"></p>

<?php
include_once("../../_tail.php");
