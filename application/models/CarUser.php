<?php

/**
 * @name CarUser.php
 * @desc Car_user 用户模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version CarUser.php v0.0 2019/8/19 新建
 */
class CarUserModel extends BaseModel
{

    private $_table = 'user_info';

    /**
     * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
     */
    public function __construct()
    {

        //调用父类的构造方法
        parent::__construct();

    }

    /**
     * 根据openID,获取用户信息
     *
     * @param    String    openid    openid
     * @return    Array    $ret    用户信息
     */
    public function getUserByOpenid($openid)
    {

        $this->_db->where('openid', $openid);
        $ret = $this->_db->getOne($this->_table);
        return $ret;
    }

    /**
     * 添加用户
     *
     * @param    Array $data 新建用户信息
     * @return    Mixed    $ret    如果成功返回新增用户ID，失败返回false
     */
    public function addUser($data)
    {

        return $this->_db->insert($this->_table, $data);

    }

    /**
     * 更新用户头像和昵称
     *
     * @param    String    openid    openid
     * @param    Array $data 用户信息
     * @return    Mixed    $ret    如果成功返回用户信息，失败返回false
     */
    public function updateUserInfo($openid, $data)
    {

        $this->_db->where('openid', $openid);
        $ret = $this->_db->update($this->_table, $data);
        if ($ret) {
            $ret = $this->getUserByOpenid($openid);
        }
        return $ret;

    }

    /**
     * 获取用户信息
     *
     * @param    Integer $id 用户id
     * @return    Array    $ret    用户信息
     */
    public function getUserInfo($id)
    {

        $this->_db->where('id', $id);
        $ret = $this->_db->getOne($this->_table);
        return $ret;

    }

    //添加用户手机号
    public function addUserPhone($user_id, $phone)
    {

        $this->_db->where('id', $user_id);
        $ret = $this->_db->update($this->_table, ['phone' => $phone, 'updated_at' => date('Y-m-d H:i:s')]);
        return $ret;

    }


}
