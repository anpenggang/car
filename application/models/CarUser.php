<?php

/**
 * @name LctUser.php
 * @desc Lecture_user 用户模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version LctUser.php v0.0 2019/8/19 新建
 */
class LctUserModel extends BaseModel {

    private $_table = 'car_user';

    /**
     * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
     */
    public function __construct() {

        //调用父类的构造方法
        parent::__construct();

    }

    /**
     * 根据openID,获取用户信息
     *
     * @param	String	openid	openid
     * @return	Array	$ret	用户信息
     */
    public function getUserByOpenid($openid) {

        $this->db->where('openid',$openid);
        $ret = $this->db->getOne($this->_table);
        return $ret;
    }

    /**
     * 添加用户
     *
     * @param	Array	$data	新建用户信息
     * @return	Mixed	$ret	如果成功返回新增用户ID，失败返回false
     */
    public function addUser($data) {

        return $this->db->insert($this->_table,$data);

    }

    /**
     * 更新用户头像和昵称
     *
     * @param	String	openid	openid
     * @param	Array	$data	用户信息
     * @return	Mixed	$ret	如果成功返回用户信息，失败返回false
     */
    public function updateUserInfo($openid,$data) {

        $this->db->where('openid',$openid);
        $ret = $this->db->update($this->_table,$data);
        if($ret) {
            $ret = $this->getUserByOpenid($openid);
        }
        return $ret;

    }

    /**
     * 获取用户信息
     *
     * @param	Integer	$id		用户id
     * @return	Array	$ret	用户信息
     */
    public function getUserInfo($id) {

        $this->db->where('id',$id);
        $ret = $this->db->getOne($this->_table);
        return $ret;

    }

    /**
     * 获取用户关注列表
     *
     * $param	Integer	$user_id	用户id
     * @return	Array	$ret		用户关注列表数组
     */
    public function getSubscribeList($user_id) {

        //获取课程相关信息
        $sql = "SELECT c.title,c.cover_url,COUNT(uc.id) AS subscribers FROM lecture_user_course uc";
        $sql .= " INNER JOiN lecture_course c ON uc.course_id = c.id";
        $sql .= " WHERE uc.user_id = {$user_id} AND uc.isdel=0 GROUP BY uc.course_id";
        $ret = $this->db->rawQuery($sql);
        return $ret;

    }

}
