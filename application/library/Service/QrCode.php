<?php 

require (APPLICATION_PATH."/application/third/phpqrcode/phpqrcode.php");

class Service_QrCode{


	public static function png($data,$filename,$logopath='',$bgcolor='#FFFFFF',$fgcolor='#000000'){
		
		$bgcolor = hexdec($bgcolor);
		$fgcolor = hexdec($fgcolor);
		$errorLevel = 'H';
		$matrixPointSize = 6 ;
		QRcode::png($data, $filename, $errorLevel, $matrixPointSize, 1, false,$bgcolor,$fgcolor);	
		if(!empty($logopath)){
			$QR = imagecreatefrompng($filename);
			$logo = imagecreatefromstring(file_get_contents($logopath));
			$QR_width = imagesx($QR);
			$QR_height = imagesy($QR);

			$logo_width = imagesx($logo);
			$logo_height = imagesy($logo);

			// Scale logo to fit in the QR Code
			$logo_qr_width = $QR_width/5;
			$scale = $logo_width/$logo_qr_width;
			$logo_qr_height = $logo_height/$scale;
			$from_width = ($QR_width-$logo_qr_width)/2; //开始位置
			imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
			imagepng($QR,$filename);
			imagedestroy($QR);
		}	
	}
}

?>
