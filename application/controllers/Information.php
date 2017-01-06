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
        $this->assign('header_name', '数据中心');
        $data = $this->frontend_model->show_information();
        $ysday = $this->frontend_model->yesterday_info();
        $jukuan = $this->frontend_model->jukuan();
        $phangcity= $this->frontend_model->phang_city();
        $phangcompany= $this->frontend_model->phang_company();
        $lminfo = $this->frontend_model->lminfo();
        $this->cismatry->assign('lminfo', $lminfo);
        $this->cismatry->assign('phangcity', $phangcity);
        $this->cismatry->assign('phangcompany', $phangcompany);
        $this->cismatry->assign('jukuan', $jukuan);
        $this->cismatry->assign('data', $data);
        $this->cismatry->assign('ysday', $ysday);
        $this->cismatry->assign('footer_flag', 5);
        $this->cismatry->display('frontend/show_information.html');
    }
}