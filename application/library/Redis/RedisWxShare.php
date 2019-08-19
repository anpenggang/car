<?php
/**
 * @name RedisLib
 * @desc sample action, 和url对应
 * @author 
 */

class RedisWxShare extends RedisBase{

	public function token_get(){

		$token_obj = new Rediska_Key('wx_token');
		$token = $token_obj->getValue();
		return $token;    
	}

	public function token_set($token){

		$token_obj = new Rediska_Key('wx_token');
		$token_obj->setValue($token);
		$token_obj->expire(3600);
	}


	public function ticket_get(){

		$ticket_obj = new Rediska_Key('wx_ticket');
		return $ticket_obj->getValue();
	}

	public function ticket_set($ticket){

		$ticket_obj = new Rediska_Key('wx_ticket');
		$ticket_obj->setValue($ticket);
		$ticket_obj->expire(3600);
	}
}
