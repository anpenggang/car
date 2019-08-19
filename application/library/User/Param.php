<?php

class Film_Param{

	
	
	public function InputParamHandle($param){

	}

	public static function OutputListParam($info,$userid){
	
	    $orderList = new Orders_List();
	    $hasbuy = $orderList->HasOrders($userid,$info['id']); 
	    $data = array(
		'documentid'=>$info['id'],
		'title'=>$info['title'],
		'pic'=>$info['pic'],
		'price'=>$info['price'],
		'purchase'=>$info['purchase']+$info['fator'],
		'hasbuy'=>0,
	    );
	    if($hasbuy){
		$data['disk'] = array(
		    'disklink'=> $info['disklink'],
		    'diskcode'=> $info['diskcode']   
		);
		$data['hasbuy'] = 1;
	    }   
	    return $data;
	}

	public static function OutputInfoParam($info,$userid){
	
	    $data = self::OutputListParam($info,$userid);
	    $data['intro'] = $info['intro'];
	    return $data;
	}
}
