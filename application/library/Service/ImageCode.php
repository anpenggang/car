<?php 
class Service_ImageCode{

	private $width ;
	private $height ;
	private $codeNum ;
	private $image ;
	private $code ;


	function __construct($code , $codeNum ,$width=60,$height=20){

		$this->code = $code ;
		$this->codeNum = $codeNum ;
		$this->width = $width ;
		$this->height = $height ;
	}

	private function createImage(){  

		$this->image=imagecreatetruecolor($this->width, $this->height);  
		//随机背景色  
		$backColor=imagecolorallocate($this->image, rand(225,255), rand(225,255), rand(225,255));  
		//为背景填充颜色  
		imagefill($this->image, 0, 0, $backColor);  
		//设置边框颜色  
		$border=imagecolorallocate($this->image, 0, 0, 0);  
		imagerectangle($this->image, 0, 0, $this->width-1, $this->height-1, $border);    
	} 

	private function setDisturbColor() {  

		$lineNum=rand(2,4);             
		for($i=0;$i<$lineNum;$i++) {  
			$x1=rand(0,$this->width/2);  
			$y1=rand(0,$this->height/2);  
			$x2=rand($this->width/2,$this->width);  
			$y2=rand($this->height/2,$this->height);  
			$color=imagecolorallocate($this->image, rand(100,200), rand(100,200), rand(100,200));    
			imageline($this->image, $x1, $y1, $x2, $y2, $color);  
		}  
	}


	private function writeTextCode(){

		$string=$this->code;  
		for($i=0;$i<$this->codeNum;$i++) {  
			$x=rand(1,4)+$this->width*$i/$this->codeNum;  
			$y=rand(1,$this->height/4);  
			$color=imagecolorallocate($this->image, rand(0,128), rand(0,128), rand(0,128));  
			$fontSize=rand(4,5);  
			imagestring($this->image, $fontSize, $x, $y, $string[$i], $color);  
		} 
	}

	private function imageCode() {  

		if(imagetypes() & IMG_GIF) {  
			header("Content-type:image/gif");  
			imagepng($this->image);  
		}else if(imagetypes() & IMG_JPG) {  
			header("Content-type:image/jpeg");  
			imagepng($this->image);  
		}else if(imagetypes() & IMG_PNG) {  
			header("Content-type:image/png");  
			imagepng($this->image);  
		}else if(imagetypes() & IMG_WBMP) {  
			header("Content-type:image/vnd.wap.wbmp");  
			imagepng($this->image);  
		}else {  
			die("PHP不支持图像创建");  
		}  
	} 

	public function outputImageCode(){

		$this->createImage();
		$this->setDisturbColor();
		$this->writeTextCode();
		$this->imageCode();
	}

}

?>
