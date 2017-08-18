<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once("./_common.php");

if ( !$member['mb_id'] ) alert("로그인 먼저 하세요.",G5_BBS_URL.'/login.php');
if ( $is_admin != 'super' ) alert("관리자만 접근 가능합니다.");

$sql = " SELECT * FROM {$__TABLE__list} ORDER BY rdate DESC ";
$res = sql_query($sql);
$numrows = @sql_num_rows($res);

$sqlTarget = " SELECT * FROM {$__TABLE__target} ORDER BY rdate ASC ";
$resTarget = sql_query($sqlTarget);
//$rowTarget = sql_fetch($sqlTarget);

$g5['title'] = 'RSS';
include_once("../_head.php");

$urlBase = "http://storefarm.naver.com";

// ======================= 사실 안 쓸 예정임 ㅋㅋ
/*
$sqlColumn = " SELECT count(*) as columns FROM information_schema.columns WHERE table_name='{$__TABLE__list}' ";
$resColumn = sql_fetch($sqlColumn);
$minusColumnsInfo = array(
	'mainImage',
);
$columns = $resColumn['columns'] - $minusColumns; // 요놈은 테이블 디자인 시 필요한 카운트
$minusColumns = count($minusColumnsInfo);
*/
// ======================= 여기까지 안 씀.

// 출력 칼럼 타이틀
$columnsTitleArr = array(
	'checkbox'=>20, // checkbox
	'No'=>26,
	'Type'=>60,
	'ID'=>120,
	'사이트제목'=>'',
	'대표이미지'=>60,
	'수집컨텐츠수'=>80,
	'등록일시'=>70,
	'최종수집일시'=>70,
);
$columnsCount = count($columnsTitleArr);

$_admin_menu_arr_ = array(
	'전체 RSS'=>'rss.php?mode=all',
);

?>

<?php if ( $is_admin == 'super' ) { ?>
<div style="padding-top:10px;">

<form method="post" action="./?mode=write">
	<table>
	<tr>
		<th>target</th>
		<th>user_id</th>
		<th>등록</th>
	</tr>
	<tr>
		<td>
			<select name="target">
			<?php for ( $t=0; $rowT = sql_fetch_array($resTarget); $t++ ) { ?>
			<option value="<?php echo $rowT['target']; ?>"><?php echo $rowT['target']; ?></option>
			<?php } // end for. ?>
			</select>
		</td>
		<td><input type="text" name="user_id" style="width:120px;" /></td>
        <td colspan="4"><input type="submit" value="등록하기" /></td>
	</tr>
	<tr>
		<td colspan="4"></td>
	</tr>
	</table>
</form>
</div>
<?php } // end if. ?>

<p style="padding-top: 10px;"></p>

<?php if ( $is_admin == 'super' ) { ?>
<div style="padding: 10px 0px;">
	<?php
	foreach ( $_admin_menu_arr_ as $linkTitle => $linkTarget ) {
	?>
	<span style="padding-right: 10px;"><button type="button" style="padding: 10px 10px;" onclick="window.open('<?php echo $linkTarget; ?>')"><?php echo $linkTitle; ?></button></span>
	<?php
	} // end foreach.
	?>
</div>
<?php } // end if. ?>

<div>
	<form id="frmList" name="frmList" method="post" action="./?mode=delete">
	<table width="100%" style="border-spacing:0px;" >
	<tr style="background-color:#f0f0f0;">
		<?php
		foreach ( $columnsTitleArr as $columnsTitle => $columnsWidth ) {

			$title = $columnsTitle;

			$styleArr = array();
			if ( $columnsWidth ) $styleArr[] = 'width: '.$columnsWidth.'px;';

			// 데이터 출력 가공.
			if ( $columnsTitle == 'checkbox' ) {
				$title = '<input type="checkbox" id="allCheck">';
				$styleArr[] = 'height: 40px;';
			} // end if.

			if ( $styleArr ) {
				$stylePress = implode(' ',$styleArr);
				$style = ' style="'.$stylePress.'"';
			} else {
				//$stylePress = implode(' ',$styleArr);
				$style = '';
			} // end if.

		?>
		<th<?php echo $style; ?>><?php echo $title; ?></th>
		<?php
		} // end foreach.
		?>
	</tr>
	<?php
	if ( $numrows > 0 ) {
		for ( $i=0; $row=sql_fetch_array($res); $i++ ) {

		$user_id = $row['user_id'];
		$title = $row['title'];

		$rowData = sql_fetch(" SELECT count(*) as count FROM {$__TABLE__data} WHERE user_id='".$row['user_id']."' AND target='".$row['target']."' ");
		$scrapCount = number_format($rowData['count']);
		if ( $scrapCount > 0 ) {
			if ( $row['target'] == 'naverblog' || $row['target'] == 'tistory' ) {
				$scrapCount = '<a href="rss.php?user_id='.$row['user_id'].'&target='.$row['target'].'&pure=yes" target="_blank" style="font-weight: Bold; color: #ff0099; font-size:16px;">'.$scrapCount.'</a>';
			} else {
				$scrapCount = '<a href="rss.php?user_id='.$row['user_id'].'&target='.$row['target'].'" target="_blank" style="font-weight: Bold; color: #ff0099; font-size:16px;">'.$scrapCount.'</a>';
			}
		}

		$link = '#';
		if ( $row['target'] == 'storefarm' ) {
			$link = 'http://storefarm.naver.com/'.$row['user_id'];
		} else if ( $row['target'] == 'naverblog' ) {
			$link = 'http://blog.naver.com/'.$row['user_id'];
		} else if ( $row['target'] == 'naverpost' ) {
			$link = 'http://post.naver.com/'.$row['user_id'];
		} else if ( $row['target'] == 'tistory' ) {
			$link = 'http://'.$row['user_id'].'.tistory.com/category';
		} // end if.

		$rdate = $row['rdate'];
		$rdate1 = substr($rdate, 0, 10);
		$rdate2 = substr($rdate, -8);

		$last_scrap_datetime = $row['last_scrap_datetime'];
		$ldate1 = substr($last_scrap_datetime, 0, 10);
		$ldate2 = substr($last_scrap_datetime, -8);

		if ( $title == '야한언니' ) $title = $user_id;

	?>
	<tr>
		<td class="cust_txt_center" style="padding: 15px 0;;"><input type="checkbox" name="chk[<?php echo $row['user_id']; ?>]" value="<?php echo $row['target']; ?>" /></td>
		<td class="cust_txt_center"><?php echo $numrows; ?></td>
		<td class="cust_txt_center"><?php echo $row['target']; ?></td>
		<td class="cust_txt_center"><a href="<?php echo $link; ?>" target="_blank"><?php echo $user_id; ?></a></td>
		<td class="cust_txt_center"><a href="<?php echo $link; ?>" target="_blank"><?php echo $title; ?></a></td>
		<td class="cust_txt_center"><a href="<?php echo $link; ?>" target="_blank"><img src="<?php echo $row['mainImage']; ?>" style="height:26px;" title="<?php echo $row['mainImage']; ?>" /></a></td>
		<td class="cust_txt_center"><?php echo $scrapCount; ?></td>
		<td class="cust_txt_center"><?php echo $rdate1.'<br />'.$rdate2; ?></td>
		<td class="cust_txt_center"><?php echo $ldate1.'<br />'.$ldate2; ?></td>
	</tr>
	<?php
		$numrows--;
		} // end for.
	} else {
	?>
	<tr>
		<td colspan="<?php echo $columnsCount; ?>" style="text-align: center; height: 200px;">출력 데이터 없음.</td>
	</tr>
	<?php
	} // end if.
	?>
	<tr>
		<td colspan="<?php echo $columnsCount / 2 ; ?>"><input type="submit" value="삭제" style="padding: 10px 10px;" /><span style="padding-left:20px;"></span><input type="button" class="truncate" value="비우기" style="padding: 10px 10px;" /></td>
        <td colspan="<?php echo ceil ( $columnsCount / 2 ) ; ?>" style="text-align: right;"><input type="button" class="scrapStart" value="수동 수집시작" style="padding: 10px 10px;" /> <input type="button" class="scrapReStart" value="수동 재수집시작" style="padding: 10px 10px;" /></td>
	</tr>
	</table>
	</form>
</div>

<div style="text-align:right;padding-top:10px;">

</div>


<p style="padding: 50px 0;"></p>

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
		$(location).attr("href","./?mode=order"); // scrap.php 코드 정상 작동 확인.
	});

	// 수동 재수집시작
	$(".scrapReStart").click( function() {
		//$(location).attr("href","/11street.my/?mode=scrap&manual=manual"); // scrap.php 코드 정상 작동 확인.
		$(location).attr("href","./?mode=order&re=yes"); // scrap.php 코드 정상 작동 확인.
	});

	// 비우기
	$(".truncate").click( function() {
		if ( confirm("모든 데이터를 비우시겠습니까?") == true ) {
			$(location).attr("href","./?mode=delete&truncate=truncate"); // scrap.php 코드 정상 작동 확인.
		}
	});

});

</script>

<?php
include_once("../_tail.php");
