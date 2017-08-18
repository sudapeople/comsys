<?php
include_once("./_common.php");

function scrap($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	#curl_setopt($ch, CURLOPT_POST, true);
	#curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 바로 출력 없음.
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

$target = 'http://storefarm.naver.com';
$store_id = $_GET['store_id'];
if ( !$_GET['store_id'] ) $store_id = 'gbseller808';
$store_url_param = '/category/ALL';

$result = '';
//$result = scrap($target.'/'.$store_id.$store_url_param.'?size=80');
$result = scrap($target.'/'.$store_id.$store_url_param.'?size=80');

preg_match("@<span class=\"last_depth\">.*<em>\([^<]+<span[^>]+>([^<]+)</span>@Us",$result,$match);

$total_count = array_pop($match);
$total_count = preg_replace("/[^0-9]*/s", "", $total_count);

$t_page = $total_count / 40;
$t_page = ceil($t_page);

#echo '<div>'.$t_page.'</div>';

########################################################
## 상품 정보 수집
########################################################

#print_r2($result); exit;

//preg_match("@<ul class=\"lst\">(.*)</ul>@Us",$result,$match_list);
preg_match("@<ul class=\"lst\">(.*)</ul>@Us",$result,$match_list);

#print_r($match_list); exit;

$product_list_source = array_pop($match_list);

#echo($product_list_source); exit;

#preg_match_all("@<dl class=\"info\">(.*)</dl>@Us",$result,$match_products);
preg_match_all("@<li>(.*)</li>@Us",$product_list_source,$match_products);

$array_products_info = array_pop($match_products);

#print_r($array_products_info);

$db_table_name = 'dev_scrap_storefarm';

foreach ( $array_products_info as $key => $product_info ) {

    #echo '<xmp>'.$product_info.'</xmp>'; exit;

    preg_match("@href=\"([^\"]+)\"@Us",$product_info,$match_url);
    $product_url = array_pop($match_url);
	$product_url = $target.$product_url;

    $product_code = array_pop(explode("/",$product_url));
    #echo '<div>'.$product_code.'</div>';

    preg_match("@src=\"([^\"]+)\"@Us",$product_info,$match_src);
    $product_img = array_pop($match_src);

    preg_match("@alt=\"([^\"]+)\"@Us",$product_info,$match_title);
    $product_name = array_pop($match_title);

    $match_data_count = sql_fetch(" select count(product_code) as cnt from {$db_table_name} where product_code='{$product_code}' ");

	#echo '<div>'.$match_data_count['cnt'].'</div>'; exit;

	$view_info = array(
		'$product_name' => $product_name ,
		'$product_url' => $product_url ,
		'$product_img' => $product_img ,
		'$product_code' => $product_code ,
	);

	//print_r($view_info); exit;

    if ( $match_data_count['cnt'] > 0 ) {
        sql_query(" update {$db_table_name} set product_name='{$product_name}' , product_link='{$product_url}' , product_img='{$product_img}' , udate=now() where product_code='{$product_code}' ");
    } else {
        sql_query(" insert into {$db_table_name} set store_id='{$store_id}' , product_name='{$product_name}' , product_code='{$product_code}' , product_img='{$product_img}' , product_link='{$product_url}' , rdate=now() ");
    }

/*
    echo '<div>product_url : '.$product_url.'</div>';
    echo '<div>product_name : '.$product_name.'</div>';
    echo '<br/><br/>';
*/

} // end foreach.

echo date("Y-m-d H:i:s")." : OK\n";
