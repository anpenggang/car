<?php

//123
define('APPLICATION_PATH', dirname(__FILE__));
require_once APPLICATION_PATH.'/application/third/aliyun-php-sdkv2-20130815/aliyun.php';
require_once APPLICATION_PATH.'/application/library/LaneWeChat/lanewechat.php';
$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");
$application
	->bootstrap()
	->run();
