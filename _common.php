<?php
include_once('./common.php');

//if ( !$_SESSION['ss_mb_id'] ) alert("로그인 먼저 하세요.",G5_BBS_URL.'/login.php');

// 커뮤니티 사용여부
if(G5_COMMUNITY_USE === false) {
    if (!defined('G5_USE_SHOP') || !G5_USE_SHOP)
        die('<p>쇼핑몰 설치 후 이용해 주십시오.</p>');

    define('_SHOP_', true);
}
?>
