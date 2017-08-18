<?php
include_once("./_common.php");

if ( $member['mb_level'] < $enter_level || !$member['mb_id'] ) {
	alert('접근이 불가능합니다.',G5_URL);
}

$g5['title'] = '마케팅 URL 등록';
include_once("./_head.php");

$user = $member['mb_id'];

$limitRes = sql_fetch(" select mb_1 from {$g5['member_table']} where mb_id = '{$user}' ");
$limitCount = $limitRes['mb_1'];
if ( !$limitCount ) $limitCount = 10;

$sql = " select * from dev_rss where rss_member = '{$user}' order by rss_id desc ";
$result = sql_query($sql);
$result_count = sql_num_rows($result);

$rss_href = 'rss.php?user='.$user;

$colspan = 3;
?>

<link rel="stylesheet" href="./dev.css">

<div class="local_desc01 local_desc">
    <p><strong>&lt;안내&gt;</strong> 현재 베타서비스이며 URL 등록은 <strong><?php echo $limitCount; ?>개</strong>까지 가능합니다.</p>
</div>

<ul class="btn_bo_user">
	<?php if ( $is_admin ) { ?><li><a href="./adm.rss.userList.php" target="_self" class="btn_b01">ADM</a></li><?php } ?>
	<li><a href="<?php echo $rss_href ?>" target="_blank" class="btn_b01">RSS</a></li>
</ul>

<form name="frm_add" method="post" action="./rss.delete.php">
<div id="menulist" class="tbl_head01 tbl_wrap">
    <table>
    <thead>
    <tr>
        <th width="60" scope="col">번호</th>
        <th scope="col">링크</th>
        <th width="120" scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
	$num = $result_count;
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
		$rss_url = $row['rss_url'];
		$rss_id = $row['rss_id'];
	?>
    <tr class="menu_list">
		<input type="hidden" name="rss_id" value="<?php echo $rss_id; ?>" />
        <td class="menu_list_num"><?php echo $num; ?></td>
        <td class=""><a href="<?php echo $rss_url; ?>" target="_blank"><?php echo $rss_url; ?></a></td>
        <td class="td_mng">
            <button type="button" class="btn_del_menu">삭제</button>
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
</form>

<form name="frm_add" method="post" action="./rss.write.php">
<h2 class="frm_add">URL 추가</h2>
<div id="menulist" class="tbl_head01 tbl_wrap">
    <table>
    <tbody>
    <tr class="menu_list">
        <td class=""><input type="text" name="rss_url" class="rss_add_style" /></td>
        <td class="td_mng">
            <input type="submit" class="btn_add_menu" value="추가">
        </td>
    </tr>
    </tbody>
    </table>
</div>
</form>

<script>
$(function() {
    $(document).on("click", ".btn_del_menu", function() {
        if(!confirm("URL을 삭제하시겠습니까?"))
            return false;

		var code = $(this).closest("tr").find("input[name='rss_id']").val();

		$.post("./rss.delete.php",
			{
				rss_id:code
			},
			function()
			{
				location.reload(true);
			}
		);

    });
});
</script>

<?php
include_once("./_tail.php");
?>
