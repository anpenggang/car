<?php

/**
 *
 * @name LctIndex.php
 * @desc Lecture_Index 主页模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version LctIndex.php v0.0 2018/4/12 新建
 */
class LctIndexModel extends BaseModel {

	private $_table = 'lecture_user';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}


}//endclass
