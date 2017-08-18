<?php
include_once("./_common.php");

if ( !$member['mb_id'] ) alert("잘못된 접근입니다.");

$member_id = $member['mb_id'];

$limit = ' LIMIT 10 ';
$sql = " SELECT * FROM {$_db_navercafe_scrap_data} WHERE member_id='{$member_id}' ORDER BY member_id ASC , cafe_id ASC , naver_id ASC {$limit} ";
$res = sql_query($sql);
$resNUM = sql_num_rows($res);

if ( $resNUM == 0 ) alert("데이터가 없습니다.");

$g5['title'] = '네이버 카페 아이디 추출 - 리스트';
include_once("../../_head.php");
?>

<style>
	.pdt10 {padding-top:10px;}
    .pdt30 {padding-top:30px;}
	.pdt50 {padding-top:50px;}

    .pd10 {padding: 10px 10px;}

	.center {text-align: center;}
	.bd {font-weight: bold;}

	.number {font-size: 14px; color: #ff168f;}
</style>

<p class="pdt10"></p>

<section id="download">
    <button class="btn_list pd10">이메일 리스트(CSV)</button>
	<button class="btn_list_mailchimp pd10">메일침프 MailChimp 양식(CSV)</button>
    <!-- <button class="btn_google pd10">구글 단체 이메일 보내기 형식(CSV)</button> -->
    <!-- <button class="btn_naver pd10">네이버 단체 이메일 보내기 형식(CSV)</button> -->
</section>

<p class="pdt10"></p>

<section id="notice">
	<p>메일침프(mailchimp) 양식으로 사용 시, 메일침프 무료사용자는 2,000 개씩 나눠서 메일침프에 등록해야 합니다.</p>
	<p>메일침프(mailchimp) 양식 First Name 항목은 네이버ID 앞에 "ID "가 추가됩니다.</p>
</section>

<p class="pdt30"></p>

<section id="list">
	<table cellspacing="0">
	<tr style="height:40px; background:#f0f0f0;">
		<th style="width: 40px;">NO</th>
		<th style="width: 80px;">카페 ID</th>
		<th style="width: 300px;">카페명</th>
		<th style="width: 200px;">카테고리명</th>
		<th style="width: 100px;">수집</th>
	</tr>
	<?php
	$listSQL = " SELECT * FROM {$_db_navercafe_scrap_schedule} WHERE member_id='{$member_id}' ORDER BY rdate ASC ";
	$listRES = sql_query($listSQL);
	$num = 1;
	$tot = 0;
	for ( $i=0; $listROW=sql_fetch_array($listRES); $i++ ) {
		$cafe_id = $listROW['cafe_id'];
			$view_cafe_id = 'http://cafe.naver.com/'.$cafe_id;
		$cate_title = $listROW['cate_title'];
		$target_url = $listROW['target_url'];
			$view_target_url = 'http://cafe.naver.com/'.$cafe_id.$target_url;
		$is_scrap = $listROW['is_scrap'];
		$countRES = sql_query(" SELECT * FROM {$_db_navercafe_scrap_data} WHERE member_id='{$member_id}' AND cafe_id='$cafe_id' ");
		$scrap_count = sql_num_rows($countRES);
		$tot += $scrap_count;

		$cafeRES = sql_fetch(" SELECT cafe_title FROM {$_db_navercafe_list} WHERE cafe_id='{$cafe_id}' ");
		$cafe_title = $cafeRES['cafe_title'];

	?>
	<tr style="height:30px;">
		<td class="center"><?php echo $num; ?></td>
		<td class="center"><a href="<?php echo $view_cafe_id; ?>" target="_blank"><?php echo $cafe_id; ?></a></td>
		<td class="center"><a href="<?php echo $view_cafe_id; ?>" target="_blank"><?php echo $cafe_title; ?></a></td>
		<td class="center"><a href="<?php echo $view_target_url; ?>" target="_blank"><?php echo $cate_title; ?></a></td>
		<td class="center"><?php echo number_format($scrap_count); ?></td>
	</tr>
	<tr><td colspan="100" style="border-top:1px dashed #555;"></td></tr>
	<?php
	$num++;
	} // end for.
	?>
	<tr style="height:30px;">
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td class="center bd number"><?php echo number_format($tot); ?></td>
	</tr>
	</table>
</section>

<p class="pdt30"></p>

<section id="notice">
    <p>아래 리스트는 샘플입니다.</p>
</section>

<p class="pdt30"></p>

<table cellspacing="0">
<tr style="background-color:#f0f0f0; height: 40px;">
    <th style="width: 40px;">NO</th>
    <th style="width: 100px;">카페 ID</th>
	<th style="width: 80px;">카테고리명</th>
    <th style="width: 300px;">이메일</th>
</tr>
<?php
$num = 1;
for ( $i=0; $row=sql_fetch_array($res); $i++ ) {

    $cafe_id = $row['cafe_id'];
	$cate_title = $row['cate_title'];
    $naver_id = $row['naver_id'];
        $email = $naver_id.'@naver.com';

?>
<tr style="height: 30px;">
    <td class="center"><?php echo $num; ?></td>
	<td class="center"><?php echo $cafe_id; ?></td>
    <td class="center"><?php echo $cate_title; ?></td>
    <td class="center"><?php echo $email; ?></td>
</tr>
<tr><td colspan="100" style="border-top:1px dashed #111;"></td></tr>
<?php
$num++;
} // end for.
?>
</table>

<p class="pdt50"></p>

<script>
    $(".btn_list").click( function() {
        location.href = 'download.php?member_id=<?php echo $member['mb_id']; ?>&type=list';
    });
	$(".btn_list_mailchimp").click( function() {
        location.href = 'download.php?member_id=<?php echo $member['mb_id']; ?>&type=list_mailchimp';
    });
</script>

<?php
include_once("../../_tail.php");
