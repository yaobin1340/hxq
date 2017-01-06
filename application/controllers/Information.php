<?php
/**
 * Created by PhpStorm.
 * User: yaobin
 * Date: 17/1/6
 * Time: 14:49
 */
class Information extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('frontend_model');
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
}