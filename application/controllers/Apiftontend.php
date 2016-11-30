<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Apiftontend extends CI_Controller {

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
		$this->load->model('Apiftontend_model');
		$this->load->library('apifun');
		//var_dump($this->apifun->get_token());
	}
//这里是 操作token 使用的

//这里结束
	public function index()
	{
		/*$uid = 4;
		$token = $this->set_token_uid($uid);*/
		//$token= $this->token;
		/*var_dump($token);
		var_dump($this->get_token_uid($token));*/
		/*$this->assign('header_name', '用户中心');
		$sum_count = $this->user_model->sum_count();
		$this->assign('sum_count', $sum_count);
		$this->display('user/user_center.html');*/
		//echo '123';
	}

	public function check_login(){
		$user = $this->Apiftontend_model->check_login();
		if($user > 0){
			$token= $this->apifun->set_token_uid($user);
			$rs = array(
				'success'=>true,
				'token'=>$token,
				'error_msg'=>''
			);
		}else{
			$rs = array(
				'success'=>false,
				'error_msg'=>'登陆失败'
			);
		}
		echo json_encode($rs);
		die();
	}
}
