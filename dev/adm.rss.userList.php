<?php
include_once("./_common.php");

if ( $member['mb_level'] < $enter_level || !$member['mb_id'] ) {
	alert('접근이 불가능합니다.',G5_URL);
}

$g5['title'] = '마케팅 URL 관리자 페이지';
include_once("./_head.php");

$user = $member['mb_id'];

$sql = " select * from dev_rss group by rss_member ";
$result = sql_query($sql);
$result_count = sql_num_rows($result);

$rss_href = 'rss.php';

$admSetBtn = array(
	'초기화'=>'reset',
	'사용중지'=>'leveldown',
);

$colspan = 3;
?>

<link rel="stylesheet" href="./dev.css">

<div class="local_desc01 local_desc">
    <p><strong>&lt;안내&gt;</strong> 이 페이지는 관리자만 볼 수 있습니다.</p>
</div>

<ul class="btn_bo_user">
	<?php if ( $is_admin ) { ?><li><a href="./adm.rss.userList.php" target="_self" class="btn_b01">ADM</a></li><?php } ?>
	<li><a href="<?php echo $rss_href ?>?user=<?php echo $user; ?>" target="_blank" class="btn_b01">RSS</a></li>
</ul>

<div id="menulist" class="tbl_head01 tbl_wrap">
    <table>
    <thead>
    <tr>
        <th width="60" scope="col">번호</th>
        <th scope="col">링크</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
	$num = $result_count;
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
		$rss_member = $row['rss_member'];
	?>
    <tr class="menu_list">
        <td class="menu_list_num"><?php echo $num; ?></td>
        <td class=""><a href="<?php echo G5_URL.'/dev/rss.php?user='.$rss_member; ?>" target="_blank"><?php echo G5_URL.'/dev/rss.php?user='.$rss_member; ?></a>
		</td>
        <td class="td_mng">
		<?php foreach ( $admSetBtn as $btnName => $btnType ) { ?>
		<span class="btn"><a href="./adm.rss.userList.update.php?mb_id=<?php echo $rss_member; ?>&type=<?php echo $btnType; ?>"><?php echo $btnName; ?></a></span>
		<?php } ?>
		</td>
    </tr>
    <?php
	$num--;
    }

    if ($i==0)
        echo '<tr id="empty_menu_list"><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<?
include_once("./_tail.php");
?>
