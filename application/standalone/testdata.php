<?php


define('APPLICATION_PATH', "/alidata/www/ben/");
include(APPLICATION_PATH . "application/library/MysqliDb2.php");

$host="112.74.53.204";
$user="Indiana_w";
$datebase="Indiana";
$pass="indiana123w";

global $DB ;

$DB = MysqliDb2::getInstance($host, $user, $pass , $datebase);



$goods = array(
	array(
		'name'=>'联想 Miix4 精英版 二合一平板',
		'pic'=>'http://img13.360buyimg.com/n2/jfs/t2749/51/1453515556/180042/72e5cc1/573db815N5679eee3.jpg',
		'intro'=>'联想 Miix4 精英版 二合一平板',
		'personlimit'=>5000,
		'phase'=>20,
		'phasetotal'=>10000,
		'category'=>2,
		'addtime'=>time(),
		'uptime'=>time(),
	),

	array(
		'name'=>'华为（HUAWEI）MateBook',
		'pic'=>'http://img11.360buyimg.com/n2/jfs/t2857/200/1483225385/250204/f7d1a32/573fd860Nfa7fcc79.jpg',
		'intro'=>'华为（HUAWEI）MateBook 12英寸平板二合一笔记本电脑 (Intel core m3 4G内存 128G存储 键盘 Win10)香槟金',
		'personlimit'=>5500,
		'phase'=>20,
		'phasetotal'=>10000,
		'category'=>2,
		'addtime'=>time(),
		'uptime'=>time(),
	),
	array(
		'name'=>'【pc平板二合一】伟卓',
		'pic'=>'http://img13.360buyimg.com/n2/jfs/t2353/66/2633115625/261842/e3be088e/56e626caN59cd6a34.jpg',
		'intro'=>'【pc平板二合一】伟卓(Venturer)平板电脑10.1英寸（64G 四核 Win10） 黑色 32GB',
		'personlimit'=>2200,
		'phase'=>20,
		'phasetotal'=>10000,
		'category'=>2,
		'addtime'=>time(),
		'uptime'=>time(),
	),
	array(
		'name'=>'佳能（Canon）EOS 80D',
		'pic'=>'http://img10.360buyimg.com/n2/jfs/t1969/313/2781963554/294943/adf1d376/56ef84b8Na9b9f999.jpg',
		'intro'=>'佳能（Canon）EOS 80D 单反套机（EF-S 18-135mm f/3.5-5.6 IS USM镜头）',
		'personlimit'=>10000,
		'phase'=>202,
		'phasetotal'=>10000,
		'category'=>2,
		'addtime'=>time(),
		'uptime'=>time(),
	),
	array(
		'name'=>'尼康（Nikon） D7100',
		'pic'=>'http://img10.360buyimg.com/n2/jfs/t2230/76/2690293828/122589/cdf84d60/56ef634fN9fb50a79.jpg',
		'intro'=>'尼康（Nikon） D7100 单反双镜头套机（18-140mmf/3.5-5.6G 镜头 + DX 35mm f/1.8G自动对焦镜头）黑色',
		'personlimit'=>8000,
		'phase'=>212,
		'phasetotal'=>10000,
		'category'=>2,
		'addtime'=>time(),
		'uptime'=>time(),
	),
	array(
		'name'=>'森海塞尔（Sennheiser）',
		'pic'=>'http://img12.360buyimg.com/n2/jfs/t1042/65/865329266/98063/18956e28/5550605bNd045f40b.jpg',
		'intro'=>'森海塞尔（Sennheiser） MOMENTUM i 大馒头2代 头戴式包耳高保真立体声耳机 苹果版 黑色',
		'personlimit'=>21000,
		'phase'=>20,
		'phasetotal'=>10000,
		'category'=>2,
		'addtime'=>time(),
		'uptime'=>time(),
	),
);


foreach($goods as $k => $item){
	$DB->insert('goods',$item);
}

echo "success";

?>
