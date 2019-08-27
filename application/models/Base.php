<?php

/**
 *
 * @name BaseModel
 * @desc 用户模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Base.php v0.0 2019/8/19 新建
 */

class BaseModel
{


    protected $_db = null; //数据库实例
    protected $config = null;//全局的配置文件

    /**
     * 构造方法
     *
     **/
    public function __construct($specify_mode = "")
    {

        $this->config = new Yaf_Config_Ini(APPLICATION_PATH . "/conf/application.ini", 'product');
        if ($this->_db === null) {
            $this->_db = new MysqliDb($this->config->db->host, $this->config->db->user, $this->config->db->pass, $this->config->db->dbname, $this->config->db->port);
        }

    }

    /**
     * 接口调用方法,包含get,post方法
     */
    public function RequestHttp($url, $data)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return json_decode($output, true);
    }

    /**
     * 根据userid 来构造session
     */
    public function SessionidGet($userid = '3288#tzsiym')
    {

        $expires = time() + 86400;
        return $expires . '_' . substr(md5($expires . 'dongdongqiang' . $userid), 0, 20) . '_' . $userid;
    }

    /**
     * 新短信渠道，创蓝渠道
     */
    public function smssend_chuanglan($phone, $content)
    {

        $post_data = array();
        $post_data['account'] = iconv('GB2312', 'GB2312', "vip_hbxy");
        $post_data['pswd'] = iconv('GB2312', 'GB2312', "Tch123456");
        $post_data['mobile'] = $phone;
        //$post_data['needstatus'] = true ;
        $post_data['msg'] = mb_convert_encoding("$content", 'UTF-8', 'auto');
        $url = 'http://222.73.117.156/msg/HttpBatchSendSM?';
        $o = "";
        foreach ($post_data as $k => $v) {
            $o .= "$k=" . urlencode($v) . "&";
        }
        $post_data = substr($o, 0, -1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $co = substr($result, 15, 1);
        return $co == 0 ? 1 : 0;
    }

    //class
}
