<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends MY_Controller {

	/**
	 * Index Page for this controller.
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function __construct()
	{
		parent::__construct();
		if(!$this->session->userdata('uid')){
			$this->show_message('请先登陆~',site_url('/frontend/login'));
		}
		$this->load->model('shop_model');
		$shop_info = $this->shop_model->get_shop_info();
		if(!$shop_info){
//			$this->show_message('您还不是商家,请先申请入驻',site_url('/frontend/register_shop'));
		}
		$this->assign('user_info', $shop_info);
	}

	public function index()
	{
//		die('ggg');
		$this->display('user/user_center.html');
	}

	public function list_order_load(){

	}
//
	public function shop_center(){
        $this->display('shop/shop_center.html');
    }

}
