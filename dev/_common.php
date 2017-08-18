<?php
include_once('../common.php');

# dev_config 설정값 호출
$sql_dev_cf = " select * from dev_config where cf_id = 1 ";
$res_dev_cf = sql_fetch($sql_dev_cf);
$enter_level = $res_dev_cf['cf_enter_level'];

/*
if ( !$member['mb_id'] ) {
	alert('접근이 불가능합니다.',G5_URL);
}
*/

?>
