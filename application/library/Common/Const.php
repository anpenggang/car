<?php

/**
 * @name Common_Const
 * @desc 系统常量设置控制器 
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Const.php v0.0 2017/9/30 新建 添加 $cdp_user_info
 * @version Const.php v1.0 2018/3/15 	  添加 $Ptn_helper
 */

class Common_Const{

	//弹幕审核登录用户名和密码
	public static $cdp_user_info =  [
		'admin_1' => 'admin_sadfd',
		'admin_2' => 'admin_zcvvc',
		'admin_3' => 'admin_qewrd',
		'admin_4' => 'admin_uiopu',
 		'admin_5' => 'admin_jklkl',
 		'admin_6' => 'admin_jujko',
		'admin_7' => 'admin_tyvgh',
 		'admin_8' => 'admin_ioklp',
 		'admin_9' => 'admin_iuijl',
		'admin_10' => 'admin_duhbq'
	];

	//代理小程序帮助信息
	public static $Ptn_helper = [
		
		'1.如何赚钱？' => [
					'1）在“代理任务-可接任务”页，点击任务进行抢单。 ',
					'注：抢单后只有在“任务详情”页面点击“接单”才算成功接单，接单后即可开始执行。',
					'2）执行完成后，在“任务详情-提交内容”页面，填写并提交反馈信息。审核通过后奖金即可实时到账。'
				],
		'2.如何提现?' => [
					'1）在“我的”页，点击进入“账户提现”',
					'2）在“账户提现-申请提现”栏输入提现金额并提交，工作人员将在3个工作日内进行审核并转账'
				],
		'3.如何邀请代理?' => [
					'1）在“我的”页，点击进入“邀请任务”',
					'2）在“邀请任务”页面，点击“一键生成邀请海报”，生成邀请海报',
					'3）其他人通过扫邀请海报里的二维码，即可进入小程序完成邀请关系绑定',
					' 4）注：邀请奖金金额等于被邀请人完成的第一个任务所得佣金的10%（例如：被邀请人完成的第一个任务佣金为100元，则被邀请人得到100元，邀请人得到10元）',
				],
	];


}//class     

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
