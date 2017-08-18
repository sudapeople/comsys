<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once("./_common.php");

$sql = " select * from {$_11street_my_list_table} order by idx desc ";
$res = sql_query($sql);
$numrows = @sql_num_rows($res);

$g5['title'] = '11street.my 상품삭제대행';
include_once("../_head.php");
?>

<?php if ( $is_admin == 'super' ) { ?>
<div style="padding-top:20px;">
<form method="post" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>?mode=write">
	<table>
	<tr>
		<th>cust_id</th>
		<th>target_url</th>
		<th>target_count</th>
	</tr>
	<tr>
		<td><input type="text" name="cust_id" style="width:120px;" /></td>
		<td><input type="text" name="target_url" style="width:440px;" /></td>
		<td><input type="text" name="target_count" style="width:60px;" /></td>
        <td colspan="4"><input type="submit" value="등록하기" /></td>
	</tr>
	<tr>
		<td colspan="4"></td>
	</tr>
	</table>
</form>
</div>
<?php } // end if. ?>

<p style="padding-top: 40px;"></p>

<div>
	<form id="frmList" name="frmList" method="post" action="/11street.my/?mode=delete">
	<table>
	<tr>
		<th><input type="checkbox" id="allCheck"></th>
		<th>no</th>
		<th style="width:90px;">cust_id</th>
		<th style="width:440px;">target_url</th>
		<th style="width:60px;">target_count</th>
		<th style="width:60px;">csv</th>
	</tr>
	<?php
	if ( $numrows > 0 ) {
		for ( $i=0; $row=sql_fetch_array($res); $i++ ) {
			$cust_id = $row['cust_id'];
			$target_url = $row['target_url'];
			$target_count = $row['target_count'];

			$target_url_icon = ' <a href="'.$target_url.'" target="_blank">LINK</a>';
	?>
	<tr>
		<td style="padding:10px 0;text-align:center;"><input type="checkbox" name="idx[]" value="<?php echo $row['idx']; ?>" /></td>
		<td style="text-align: center"><?php echo $numrows; ?></td>
		<td><input type="text" value="<?php echo $cust_id; ?>" style="width:90px;" /></td>
		<td><input type="text" value="<?php echo $target_url; ?>" style="width:400px;" /><?php echo $target_url_icon; ?></td>
		<td><input type="text" value="<?php echo $target_count; ?>" style="width:60px;" /></td>
		<td><?php if ( $row['finished'] == 'yes' ) { ?><a href="download.csv.php?cust_id=<?php echo $cust_id; ?>">DOWN</a><?php } // end if. ?></td>
	</tr>
	<?php
		$numrows--;
		} // end for.
	} else {
	?>
	<tr>
		<td colspan="6" style="text-align: center;height:100px;">출력 데이터 없음.</td>
	</tr>
	<?php
	} // end if.
	?>
	<tr>
		<td colspan="4"><input type="submit" value="삭제" /><span style="padding-left:20px;"></span><input type="button" class="truncate" value="비우기" /></td>
        <td colspan="1"><input type="button" class="scrapStart" value="수동 수집시작" /></td>
	</tr>
	</table>
	</form>
</div>

<div style="text-align:right;padding-top:10px;"></div>

<script type="text/javascript">

$(document).ready(function(){

	// 체크박스 전체 선택
	$("#allCheck").click( function() {
		if ( $("#allCheck").prop("checked") ) {
			$("input[type=checkbox]").prop("checked",true);
		} else {
			$("input[type=checkbox]").prop("checked",false);
		}
	});

	// 수동 수집시작
	$(".scrapStart").click( function() {
		//$(location).attr("href","/11street.my/?mode=scrap&manual=manual"); // scrap.php 코드 정상 작동 확인.
		$(location).attr("href","/11street.my/?mode=order"); // scrap.php 코드 정상 작동 확인.
	});

	// 비우기
	$(".truncate").click( function() {
		if ( confirm("모든 데이터를 비우시겠습니까?") == true ) {
			$(location).attr("href","/11street.my/?mode=delete&truncate=truncate"); // scrap.php 코드 정상 작동 확인.
		}
	});

});

</script>

<?php
include_once("../_tail.php");
