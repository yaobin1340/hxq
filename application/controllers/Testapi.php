<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 17/1/19
 * Time: 下午3:03
 */
class Testapi extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data = $this->frontend_model->show_information();
        $ysday = $this->frontend_model->yesterday_info();
        $jukuan = $this->frontend_model->jukuan();
        $phangcity= $this->frontend_model->phang_city();
        $phangcompany= $this->frontend_model->phang_company();
        $lminfo = $this->frontend_model->lminfo();
        $this->cismarty->assign('lminfo', $lminfo);
        $this->cismarty->assign('phangcity', $phangcity);
        $this->cismarty->assign('phangcompany', $phangcompany);
        $this->cismarty->assign('jukuan', $jukuan);
        $this->cismarty->assign('data', $data);
        $this->cismarty->assign('ysday', $ysday);
        $this->cismarty->assign('footer_flag', 5);
        $this->cismarty->display('frontend/show_information_public.html');
    }
    public function get_openid(){
        $code = $this->input->post('code');
        $appid = 'wx2d80ea0f220b6bf5';
        $secret = '7631f33bca93efa90cf68a98cc4a98e0';
        $j_access_token=file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code");
        $a_access_token=json_decode($j_access_token,true);
        $rs = array(
            'success' => true,
            'error_msg'=>'',
            'openid'=>$a_access_token["openid"]
        );
        die(json_encode($rs));
        $access_token=$a_access_token["access_token"];
        $openid=$a_access_token["openid"];
        echo $openid;
    }
}