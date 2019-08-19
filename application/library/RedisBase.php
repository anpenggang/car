<?php
/**
 * @name RedisBase
 * @desc 缓存一些公用的方法封装
 * @author 
 */

class RedisBase{

    public $redis;
    public $db;
    
    /**
     * 记录空对象
     */
    public $a_key_empty_zset = "Logic_Empty_Keys";  

    public function __construct($mode="white"){

	$config = new Yaf_Config_Ini( APPLICATION_PATH . "/conf/application.ini", 'product');

	$db_host = $config->db->host;
	$db_user = $config->db->user;
	$db_db   = $config->db->db;
	$db_pass = $config->db->pass;
	$this->db = MysqliDb::getInstance($db_host, $db_user, $db_pass , $db_db);
	//$this->db = new MysqliDb($db_host, $db_user, $db_pass , $db_db);

	$redis_host = $config->redis->host;
	$redis_port = $config->redis->port;
	$redis_pass = $config->redis->pass;
	$redis_host2 = $config->redis->host2;
	$redis_port2 = $config->redis->port2;
	$redis_pass2 = $config->redis->pass2;
	$redis_host3 = $config->redis->host3;
	$redis_port3 = $config->redis->port3;
	$redis_pass3 = $config->redis->pass3;
	$redis_namespace = $config->redis->namespace;

	$options = array(
		'namespace' => $redis_namespace,
		'servers'   => array(
		    array('host' => $redis_host, 'port' => $redis_port,'password'=>$redis_pass),
		    array('host' => $redis_host2, 'port' => $redis_port2,'password'=>$redis_pass2),
		    array('host' => $redis_host3, 'port' => $redis_port3,'password'=>$redis_pass3),
		    )
		);

	require_once APPLICATION_PATH . '/application/library/Rediska.php';
	$this->redis = new Rediska($options);
    }


    /**
     * 对过滤条件进行加密,方便创建key
     * 比如 array('attr'=>1,'schoolid'=>1);
     */
    public function _gen_md5_filter($filter){
	$str = '';
	if(!empty($filter)){
	    ksort($filter);
	    foreach($filter as $k => $v){
		$str .= $k.'='.$v.'&';
	    }
	    return md5($str);
	}else{
	    return '';
	}
    }


    /**
     * 对id进行一次加密,用来作为查询条件（安全考虑）
     * @ $type 类型为常用的有  'topic' ,'activity' ,'club'
     * @ 命名不统一
     */
    public function _encrypt_id_to_pwd($id ,$type){
	if(empty($id) || empty($type)){
	    return false;
	}
	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES,MCRYPT_MODE_ECB),MCRYPT_RAND);
	$passcrypt = mcrypt_encrypt(MCRYPT_DES ,$type, $id, MCRYPT_MODE_ECB, $iv);
	$encode = rtrim(strtr(base64_encode($passcrypt), '+/', '-_'), '=');
	return $encode;
    }

    /**
     * 兼容以前得方法
     */
    public function GetPwdForId($id, $type){
	return $this->_encrypt_id_to_pwd($id ,$type);
    }


    /**
     * 对GetPwdForId加密id反向解密
     * @ $type 类型为常用的有  'topic' ,'activity' ,'club'
     */
    public function _decrypt_pwd_to_id($pwd , $type){
	if(empty($pwd) || empty($type)){
	    return false;
	}
	$decoded = base64_decode(str_pad(strtr($pwd, '-_', '+/'), strlen($pwd) % 4, '=', STR_PAD_RIGHT));
	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES,MCRYPT_MODE_ECB),MCRYPT_RAND);
	$decrypted = mcrypt_decrypt(MCRYPT_DES ,$type, $decoded, MCRYPT_MODE_ECB, $iv);
	return (int)$decrypted;
    }

    /**
     * 兼容以前的方法
     */
    public function GetIdFromPwd($pwd, $type){  
	return $this->_decrypt_pwd_to_id($pwd ,$type);
    }


    // key是否在 emptyset中
    public function in_empty_set( $key ){
	$empty_key = $this->a_key_empty_zset; 
	$empty_obj = new Rediska_Key_Set($empty_key);
	if( $empty_obj->isExists()){
	    $ret =  $empty_obj->exists($key);
	    return $ret;
	}
	return false;
    }  

    //某个key对应的内容为空，放入这个set中以作标记
    public function add_to_emptyset($key){
	$empty_key = $this->a_key_empty_zset; 
	$empty_obj = new Rediska_Key_Set($empty_key);
	$empty_obj[] =  $key;
    }

    /**
     * 从空对象中删除该对象
     */
    public function del_from_emptyset($key){
	$empty_key = $this->a_key_empty_zset; 
	$empty_obj = new Rediska_Key_Set($empty_key);
	if($empty_obj->exists($key) ){
	    $ret = $empty_obj->remove($key);
	}
    }


    /**
     * 获取白天/晚上的 table
     */
    public function db_table_get($mode ,$table){

	if($mode == 'white'){
	    return $table ;
	}else{
	    return $table.'_black';
	}
    }


    /**
     * 获取 redis 缓存中的key
     */
    public function redis_key_get($mode ,$key){

	if($mode == 'white'){
	    return $key ;
	}else{
	    return $mode.'_'.$key;
	}
    }

}

