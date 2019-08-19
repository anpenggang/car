<?php


// usage : php qr.php yoururl > filename.png
include "./phpqrcode/phpqrcode.php";//引入PHP QR库文件
$url = $argv[1];
$errorCorrectionLevel = "H"; // H
$matrixPointSize = "10"; // 10
QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);
