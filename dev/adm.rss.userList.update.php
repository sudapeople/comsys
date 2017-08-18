<?php
include_once("./_common.php");

$sql = "";
$where = "";

switch ( $type ) {
	case "reset" :
		$sql = " delete from dev_rss ";
		$where = " where rss_member = '{$mb_id}' ";
		break;
	case "leveldown" :
		$sql = " update {$g5['member_table']} set mb_level = 2 ";
		$where = " where mb_id = '{$mb_id}' ";
		break;
	default :
		$sql = "";
		$where = "";
		break;
} // end switch.

if ( $sql ) {
	$sql = $sql . $where;
	sql_query($sql);
} // end if.

goto_url('./adm.rss.userList.php');

?>
