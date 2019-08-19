<?php

/**
 *
 * @name UserModel
 * @desc 用户模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version User.php v0.0 2017/9/30 新建
 */
class ApgModel extends BaseModel {

    private $_table = 'barrage_users';

    /**
     * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
     */
    public function __construct() { 
    
        //调用父类构造方法
        parent::__construct();

    }   

    /** 
     * 获取用户信息 
     * @param   String  $id     用户ID
     * @return  Array   $ret    用户信息
     */
    public function getUserInfo($id) {
    
        //echo $openid;
        $sql = "SELECT * FROM {$this->_table} WHERE id='{$id}' LIMIT 1";
        $ret = $this->db->rawQuery($sql);
        return $ret;

    }
}
