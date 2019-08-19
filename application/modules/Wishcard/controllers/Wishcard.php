<?php
/**
 *
 * @name cardController
 * @desc 心愿卡控制器
 * @link mailto:lesliedream@outlook.com 
 * @version  v0.0 2018/8/2 新建                                                                 
 */	

class WishcardController extends BaseController{

	private $_model = null;

	//初始化方法
	public function init(){
	
		//调用父类的初始化方法
		parent::init();

		//初始化模型
		$this->_model = new WcCardModel();

		//根据sessionID获取用户id
        $this->_userinfo = $this->verifySessionid($this);
	}

	//获取心愿卡
	public function listAction(){

			$list = $this->_model->getAllWishcard($this->_userinfo['user_id']);
			if($list){

					return Common_Util::returnJson('20001','成功',$list);

			}else{

					return Common_Util::returnJson('20006','暂无数据');

			}
	}

	//获取我的心愿卡
	public function myCardAction(){
	
		$list = $this->_model->MyCardList($this->_userinfo['user_id']);		
		if($list){

			return Common_Util::returnJson('20001','成功',$list);	

		}else{
		
			return Common_Util::returnJson('20006','暂无数据');
			
		}
	}

	//我收藏的卡片
	public function mycollectAction(){
		
		$list = $this->_model->myCollect($this->_userinfo['user_id']);
		if($list){
			
			return Common_Util::returnJson('20001','成功',$list);
		}else{
	
			return Common_Util::returnJson('20006','暂无数据');
		}	
	}
	
	//收藏或者取消操作
	public function collectAction(){
		
		//$card_id = Common_Util::getHttpReqQuery($this,'card_id','Int','y');
		$card_id = 2;
		$ret = $this->_model->collect($this->_userinfo['user_id'],$card_id);

		if($ret){
				
			return Common_Util::returnJson('20001','操作成功');

		}else{

			return Common_Util::returnJson('20001','操作失败');

		}
	}

	//上传生成的心愿卡片
	public function uploadImgAction(){
			
			$Wish_regret = 1; //1为收藏2遗憾
			if(!isset($_FILES['files']))
					Common_Util::ReturnErrno(10006,"请选择文件");
			else
					$file = $_FILES['files'];

			if(is_uploaded_file($file['tmp_name'])) {
					$objmodel = new ObjectModel();
					$realname = $file['name'];
					$pos = strrpos($realname, ".");
					$postfix = substr($realname, $pos+1);
					$object_key = $objmodel->createPicObject($file['tmp_name'], $file['size'], $postfix);
					list($key, $bucket) = explode("|", $object_key);
					$prefix = 'dingdangqd.oss-cn-qingdao.aliyuncs.com';
					$imgsrc = "$prefix/$key";
					//图片上传成功保存数据库
					$data=[];
					$data['userid'] = $this->_userinfo['user_id'];
					$data['Wish_regret'] = $Wish_regret;
					$data['image'] = $imgsrc;
					$imageid = $this->_model->addCard($data);
					if($imageid){
						Common_Util::returnJson(20001,'上传完成',$imgsrc);
					}else{
						Common_Util::returnJson(10006,'生成心愿或遗憾卡失败');
					}
			}else{
					Common_Util::returnJson(10005,"参数错误");
			}
	}

	public function schoolListAction() {

	}


}
?>
