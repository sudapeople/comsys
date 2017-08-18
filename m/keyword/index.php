<?php
include_once("./_common.php");

// 특수문자 변환
function specialchars_replace($str, $len=0) {
    if ($len) {
        $str = substr($str, 0, $len);
    }
    $str = str_replace(array("&", "<", ">", '"', "'"), array("&amp;", "&lt;", "&gt;", "``", "`"), $str);
    return $str;
} // end function.

// 특수문자 변환
function un_specialchars_replace($str, $len=0) {
    if ($len) {
        $str = substr($str, 0, $len);
    }
    $str = str_replace(array("&amp;", "&lt;", "&gt;", "``", "`"), array("&", "<", ">", '"', "'"), $str);
    return $str;
} // end function.

//if ( $is_admin != 'super' ) alert("잘못된 접근입니다.");
if ( !$member['mb_id'] ) alert("잘못된 접근입니다.",'/bbs/login.php');

$mode = $_POST['mode'];
$_is_super_view_ = false;
if ( $mode == 'super' || $_GET['mode'] == 'super' ) {
	$_is_super_view_ = true;
} // end if.

$g5['title'] = '키워드 검색 랭킹';
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

    .keyword_match_textarea { width: 300px; height: 200px; }
    .keyword_match_result { width: 300px; height: 200px; }

    .keyword_textarea { width: 300px; height: 400px; }
    .keyword_result { width: 300px; height: 400px; }
</style>

<div id="section">
<form method="post" target="match_data_result" action="result.php">
	<table>
	<tr>
		<th class="center">매칭 키워드</th>
		<th class="center">submit</th>
        <th class="center">결과</th>
	</tr>
    <?php
    $member_id = $member['mb_id'];
    $sql = " SELECT * FROM {$_db_match} WHERE member_id='{$member_id}' ORDER BY kid ASC , match_key ASC ";
    $res = sql_query($sql);
    $resNUM = sql_num_rows($res);
    ?>
	<tr>
		<td><textarea name="match_key" class="keyword_match_textarea"><?php
        if ( $resNUM > 0 ) { // 출력할 내용이 있어야 하겠지
            $match_update_arr = array();
            for ( $i=0; $row=sql_fetch_array($res); $i++  ) {
                $o_match_key = $row['match_key'];
                $match_key = un_specialchars_replace($o_match_key);
                echo $match_key."\n";
                $match_update_arr[] = $row['rdate'];
            } // end for.
            rsort($match_update_arr);
            $match_update = $match_update_arr[0];
        } // end if.
        ?></textarea></td>
        <td><input type="submit" value="매칭데이터" class="pd20" /></td>
        <td class="keyword_match_result"><iframe src="result.php" class="keyword_match_result" name="match_data_result"></iframe></td>
	</tr>
    <tr>
        <td colspan="100">매칭 키워드 최근 업데이트 : <?php echo $match_update; ?></td>
    </tr>
	</table>
</form>
</div>

<div id="section">
<form method="post" target="result" action="result.php">
	<table>
	<tr>
		<th class="center">검색 키워드</th>
		<th class="center">submit</th>
        <th class="center">결과</th>
	</tr>
    <?php
    $member_id = $member['mb_id'];
    $sql = " SELECT * FROM {$_db_list} WHERE member_id='{$member_id}' ORDER BY kid ASC ";
    $res = sql_query($sql);
    $resNUM = sql_num_rows($res);
    ?>
	<tr>
		<td><textarea name="keyword" class="keyword_textarea"><?php
        if ( $resNUM > 0 ) { // 출력할 내용이 있어야 하겠지
            for ( $i=0; $row=sql_fetch_array($res); $i++  ) {
                $o_keyword = $row['keyword'];
                $keyword = un_specialchars_replace($o_keyword);
                echo $keyword."\n";
                $match_keyword_update_arr[] = $row['rdate'];
            } // end for.

            $lSQL = " SELECT rdate FROM {$_db_data} WHERE member_id='{$member_id}' ORDER BY rdate DESC ";
            $lRES = sql_query($lSQL);
            $match_keyword_update_arr = array();
            for ( $l=0; $lROW=sql_fetch_array($lRES); $l++ ) {
                $match_keyword_update_arr[] = $lROW['rdate'];
            } // end for.
            rsort($match_keyword_update_arr);
            $match_keyword_update = $match_keyword_update_arr[0];
        } // end if.
        ?></textarea></td>
        <td>
            <?php if ( $member_id == 'jeb' || $is_admin == 'super' ) { ?>
            <div style="padding-bottom: 10px;"><input type="checkbox" name="sch_storefarm" value="1" /> 스토어팜</div>
            <?php } // end if. ?>
            <div><input type="submit" value="키워드검색" class="pd20" /></div>
        </td>
        <td class="keyword_result"><iframe src="result.php" class="keyword_result" name="result"></iframe></td>
	</tr>
    <tr>
        <td colspan="100">검색 키워드 최근 업데이트 : <?php echo $match_keyword_update; ?></td>
    </tr>
	</table>
</form>
</div>

<p class="pdt30"></p>

<div id="section">
    <p>2017-06-06 통합 다운로드 페이지 추가</p>
    <p>2017-06-06 네이버 모바일 검색결과 추가</p>
    <p>2017-06-05 지도 카테고리 - 광고, 일반영역 분리</p>
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
