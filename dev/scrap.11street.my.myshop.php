<?php
include_once("./_common.php");

if ( !$member['mb_id'] ) {
	alert('접근이 불가능합니다.',G5_URL);
}

if ( !$is_admin ) {
	alert("관리자 외 접근불가");
}

/////////////////////////////////////////////////////////////
/*
	w 등록
	u 업데이트(수정)
*/

//print_r2($_POST);

if ( $_POST['w'] == 'w' ) {

	sql_query(" insert dev_11street set name='".$_POST['name']."' , url='".$_POST['url']."' , page='".$_POST['page']."' , rdate=now() ");
	
} else if ( $_POST['w'] == 'u' ) {

	foreach ( $_POST['info'] as $key => $info ) {
		
		// 먼저 삭제되는 데이터인지 검증
		if ( $info['del'] == 1 ) {
			// 삭제
			sql_query(" delete from dev_11street where idx = '".$info['idx']."' ");
			sql_query(" delete from dev_scrap_11street where uidx = '".$info['idx']."' ");
		} else {
			// 수정
			sql_query(" update dev_11street set name='".$info['name']."' , url='".$info['url']."' , page='".$info['page']."' , udate=now() where idx='".$info['idx']."' ");
		}
		
	} // end foreach.
	
}

///////////////////////////////////////////////////////////////////

$g5['title'] = '11street.my 마이샵 상품코드 수집 - 400개 상품 출력';
include_once("./_head.php");

///////////////////////////////////////////////////////////////////

$sql = " select * from dev_11street order by idx desc ";
$res = sql_query($sql);
$numrows = sql_num_rows($res);

?>

<script src="http://code.jquery.com/jquery-latest.js"></script>
<script src="./js/jquery.hive.js"></script>

<script>
$(function () {
    
    function _createdCallback(hive) {}

    // 수신
    function _receivedCallback(data) {
      //console.group('테스트 == * WORKER SENT AND RECEIVED MESSAGE FROM WORKER: #' + this.WORKER_ID + '  ---  ' + Math.round( +new Date() / 1000 ) );
      //console.log( data );
      //console.groupEnd();
    }

    var _workerSetup = {
      // count: 100,
      worker: 'worker.js',
      receive: _receivedCallback,
      created: _createdCallback
    };

    $.Hive.create(_workerSetup);

    /* ---------------------------------------------- */

    $('.scrapStart').click(function () {

      var $this = $(this),
          $hive = $.Hive.get();
          //message;

      //message = $this.attr('data-type') === 'string' ? $this.nextAll('code').text().trim() : JSON.parse($this.nextAll('code').text().trim());

      //console.log( message.file + '+++++++++++++++++++' );

      $( $hive ).send("");

    });

});
</script>

<div>
<form method="post" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
<input type="hidden" name="w" value="u" />
	<table width="100%">
	<tr>
		<th>삭제</th>
		<th>고객명</th>
		<th>수집 URL</th>
		<th>page</th>
		<th>csv</th>
	</tr>
	<?php
	if ( $numrows > 0 ) {
		for ( $i=0; $row=sql_fetch_array($res); $i++ ) {
	?>
	<tr>
		<td><input type="checkbox" name="info[<?php echo $row['idx'] ?>][del]" value="1" /></td>
		<input type="hidden" name="info[<?php echo $row['idx'] ?>][idx]" value="<?php echo $row['idx']; ?>" />
		<td><input type="text" name="info[<?php echo $row['idx'] ?>][name]" value="<?php echo $row['name']; ?>" style="width:60px;" /></td>
		<td><input type="text" name="info[<?php echo $row['idx'] ?>][url]" value="<?php echo $row['url']; ?>" style="width:480px;" /></td>
		<td><input type="text" name="info[<?php echo $row['idx'] ?>][page]" value="<?php echo $row['page']; ?>" style="width:60px;" /></td>
		<td><?php if ( $row['fin_scrap'] == 'yes' ) { ?><a href="scrap.11street.my.myshop.csv.php?idx=<?php echo $row['idx']; ?>">DOWN</a><?php } // end if. ?></td>
	</tr>
	<?php
		} // end for.
	} else {
	?>
	<tr>
		<td colspan="5" style="text-align: center;">출력 데이터 없음.</td>
	</tr>
	<?php	
	} // end if.
	?>
	<tr>
		<td colspan="5"><input type="submit" value="수정" /></td>
	</tr>
	</table>
</form>
</div>

<div style="text-align:right;padding-top:10px;">
<!-- 	<a class="scrapStart" href="javascript:scrapStart();" style="border:1px dashed #3437d5; padding:2px 20px;">수집시작</a> -->
	<input type="button" class="scrapStart" value="수집시작" />
</div>

<div style="padding:20px 0 30px 0;">
	<p>수집시작 버튼을 선택해도 별다른 액션은 없습니다. 해당 페이지를 그냥 닫아주세요. 백그라운드에서 작업은 이미 시작되었습니다.</p>
</div>

<div style="padding-top:20px;">
<form method="post" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
<input type="hidden" name="w" value="w" />
	<table>
	<tr>
		<th>고객명</th>
		<th>수집 URL</th>
		<th>page</th>
	</tr>
	<tr>
		<td><input type="text" name="name" style="width:60px;" /></td>
		<td><input type="text" name="url" style="width:520px;" /></td>
		<td><input type="text" name="page" style="width:60px;" /></td>
	</tr>
	<tr>
		<td colspan="3">page는 고객미니샵 총 상품수 나누기 400, 소수점 올림의 숫자임.<br/>예시) 51,358 => 51358 / 400 = 128.395 => 129 페이지</td>
	</tr>
	<tr>
		<td colspan="4"><input type="submit" value="등록하기" /></td>
	</tr>
	</table>
</form>
</div>

<script>

/*
function scrapStart() {
	$.ajax({
		type: "post",
		url: "./scrap.11street.my.myshop.proc.php",
		cache: false,
		async: true
	});
	//$.post("./scrap.11street.my.myshop.proc.php");
}
*/

</script>

<?php
include_once("./_tail.php");
?>