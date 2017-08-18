<?php
include_once("./_common.php");

//if ( $is_admin != 'super' ) alert("잘못된 접근입니다.");
if ( !$member['mb_id'] ) alert("잘못된 접근입니다.",'/bbs/login.php');

$mode = $_POST['mode'];
$_is_super_view_ = false;
if ( $mode == 'super' ) {
	$_is_super_view_ = true;
} // end if.

$g5['title'] = '네이버 카페 아이디 추출';
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

	.btn_super { font-weight: bold; color: #d50033; background: #fff; padding: 10px; }
</style>

<p class="pdt10"></p>

	<div>
		<p><h5>일반 사용자는 최대 10개까지 등록 가능합니다.</h5></p>
	</div>

<p class="pdt10"></p>

<div>
<form method="post" action="cafe.write.php">
	<table>
	<tr>
		<th>target</th>
		<th>id</th>
		<th>등록</th>
	</tr>
	<tr>
		<td>http://cafe.naver.com/</td>
		<td><input type="text" name="cafe_id" style="width:120px;" /></td>
        <td colspan="4"><input type="submit" value="등록하기" /></td>
	</tr>
	</table>
</form>
</div>

<p class="pdt30"></p>

<?php if ( $is_admin == 'super' ) { ?>
<?php if ( $_is_super_view_ === false ) { ?>
<section id="super">
	<form method="post" action="/m/naver/">
		<input type="hidden" name="mode" value="super" />
		<input type="submit" value="슈퍼모드" class="btn_super" />
	</form>
</section>
<p class="pdt10"></p>
<?php } else { // end if. ?>
<section id="super">
	<form method="post" action="/m/naver/">
		<input type="hidden" name="mode" value="" />
		<input type="submit" value="슈퍼모드 해제" class="btn_super" />
	</form>
</section>
<p class="pdt10"></p>
<?php } // end if. ?>
<?php } // end if. ?>

<div>
	<form id="frmList" name="frmList" method="post" action="list.update.php">
	<table style="width:100%;" cellspacing="0">
	<tr style="background-color:#f0f0f0; height: 40px;">
		<th style="width:40px;">NO</th>
		<th style="width:40px;"><input type="checkbox" class="chkALL" /></th>
		<?php if ( $is_admin == 'super' ) { ?>
		<th style="width:60px;">member_id</th>
		<?php } // end if. ?>
		<th style="width:200px;">cafe TITLE</th>
		<th>cafe URL</th>
		<th style="width:100px;">기능</th>
		<th style="width:80px;">등록일시</th>
	</tr>
	<?php
	$query_orderby = ' ORDER BY member_id DESC , cid DESC ';
	if ( $is_admin == 'super' ) {
		if ( $_is_super_view_ === true ) {
			$sql = " SELECT * FROM {$_db_navercafe_list} ".$query_orderby;
		} else {
			$sql = " SELECT * FROM {$_db_navercafe_list} WHERE member_id='".$member['mb_id']."' ".$query_orderby;
		} // end if.
	} else {
		$sql = " SELECT * FROM {$_db_navercafe_list} WHERE member_id='".$member['mb_id']."' ".$query_orderby;
	}
	$res = sql_query($sql);
	$numROWS = sql_num_rows($res);

	if ( $numROWS > 0 ) {

	$num = $numROWS;
	for ( $i=0; $row=sql_fetch_array($res); $i++ ) {
		$cid = $row['cid'];
		$member_id = $row['member_id'];
		$cafe_id = $row['cafe_id'];
			$view_cafe_id = 'http://cafe.naver.com/'.$cafe_id;
		$cafe_title = $row['cafe_title'];
		$rdate = $row['rdate'];
			$rdate1 = substr($rdate,0,10);
			$rdate2 = substr($rdate,-8);

		$scrap_cate_link = '?cafe_id='.$cafe_id;
		if ( $is_admin == 'super' ) {
			$scrap_cate_link = '?cafe_id='.$cafe_id.'&member_id='.$member_id;
		} // end if.
	?>
	<tr style="height:40px;">
		<td class="center"><?php echo $num; ?></td>
		<td class="center"><input type="checkbox" name="chk[]" value="<?php echo $cid; ?>" /></td>
		<?php if ( $is_admin == 'super' ) { ?>
		<td class="center"><?php echo $member_id; ?></td>
		<?php } // end if. ?>
		<td class="center"><a href="<?php echo $view_cafe_id; ?>" target="_blank"><?php echo $cafe_title; ?></td>
		<td><a href="<?php echo $view_cafe_id; ?>" target="_blank"><?php echo $view_cafe_id; ?></td>
		<td class="center"><button type="button" style="padding: 5px 5px;" onclick="window.open('/m/naver/scrap.category.php<?php echo $scrap_cate_link; ?>')">카테고리 추출</button></td>
		<td class="center"><?php echo $rdate1.'<br />'.$rdate2; ?></td>
	</tr>
	<?php
	$num--;
	} // end for.
	?>
	<tr>
		<td colspan="100">
			<input type="submit" value="선택삭제" style="padding:5px;" />
		</td>
	</tr>
	<?php
	} else {
	?>
	<tr>
		<td colspan="100" style="text-align: center; height: 200px;">출력 데이터 없음.</td>
	</tr>
	<?php
	} // end if.
	?>
	</table>
	</form>
</div>

<p class="pdt50"></p>

<script type="text/javascript">

// 체크박스 전체 선택
$(".chkALL").click( function() {
	if ( $(".chkALL").prop("checked") ) {
		$("input[type=checkbox]").prop("checked",true);
	} else {
		$("input[type=checkbox]").prop("checked",false);
	}
});

</script>

<?php
include_once("../../_tail.php");
