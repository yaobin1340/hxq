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
        echo '1';
        $data = $this->frontend_model->show_information();
        echo '2';
        $ysday = $this->frontend_model->yesterday_info();
        echo '3';
        $jukuan = $this->frontend_model->jukuan();
        echo '4';
        $phangcity= $this->frontend_model->phang_city();
        echo '5';
        $phangcompany= $this->frontend_model->phang_company();
        echo '6';
        $lminfo = $this->frontend_model->lminfo();
        echo '7';
        $this->cismatry->assign('lminfo', $lminfo);
        echo '8';
        $this->cismatry->assign('phangcity', $phangcity);
        echo '9';
        $this->cismatry->assign('phangcompany', $phangcompany);
        $this->cismatry->assign('jukuan', $jukuan);
        $this->cismatry->assign('data', $data);
        $this->cismatry->assign('ysday', $ysday);
        $this->cismatry->assign('footer_flag', 5);
        $this->cismatry->display('frontend/show_information.html');
    }
}