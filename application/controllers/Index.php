<?php

/**
 *
 * @name IndexController
 * @desc 基类控制器，所有的控制器类都继承自本基类
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Base.php v0.0 2019/8/19 新建
 **/

class IndexController extends BaseController
{

    protected $_redis = null;
    protected $admin_user_info = null;

    /**
     * 初始化方法 基类控制器被调用的时候先执行初始化方法，可作用于全局
     */
    public function init()
    {
        parent::init();
        //Yaf_Dispatcher::getInstance()->enableView();
        $this->admin_user_info = Common_Const::$adminUserList;
    }

    public function indexAction()
    {
        $arr = [
            'img_src' => "https://ss3.bdstatic.com/70cFv8Sh_Q1YnxGkpoWK1HF6hhy/it/u=4256128443,3882252856&fm=26&gp=0.jpg",
            'remark' => "",
            'width' =>  null,
            'height' =>  null
        ];
        echo json_encode($arr);exit;
        $username = Yaf_Session::getInstance()->get('username');
        if ($username == NULL) {
            $this->redirect('/index/login');
            return false;
        }
        $this->getview()->display('header.phtml');
        $this->getview()->display('index/index.phtml');
        $this->getview()->display('footer.phtml');
    }

    public function testAction()
    {
        Yaf_Dispatcher::getInstance()->enableView();

    }

    public function loginAction()
    {

        header('content-type:text/html;charset=utf-8');
//        if ($this->getRequest()->getMethod() == 'POST') {
        if (true) {

            $username = Common_Util::getHttpReqQuery($this, 'username', 'Str', 'n');
            $password = Common_Util::getHttpReqQuery($this, 'password', 'Str', 'n');
            if(!empty($this->admin_user_info[$username]) && $this->admin_user_info[$username] == $password) {
                return $this->ajaxReturn(0,'ok');
            } else {
                return $this->ajaxReturn(-1,'用户名或者密码错误');
            }
        }

    }

    public function fileAction()
    {

        $this->fileUpload();
        exit;

    }

    public function logoutAction()
    {

        Yaf_Session::getInstance()->del('username');
        Yaf_Session::getInstance()->del('user_uuid');
        //Yaf_Session::getInstance()->distroy();
        header("Location:/");
        return false;

    }


}
