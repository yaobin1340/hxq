<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "MY_APIcontroller.php";
class Apiuser extends MY_APIcontroller {

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
	private $token;
	private $app_uid;
	private $rs = array(
		'success'=>true,
		'error_msg'=>''
	);
	private $err_rs = array(
		'success'=>false,
		'error_msg'=>''
	);
	public function __construct()
	{
		parent::__construct();
		$this->load->model('apiuser_model');
		$this->token = $this->get_token();
		if($this->token){
			$this->app_uid=$this->get_token_uid($this->token);
			if($this->app_uid <= 0){
				$this->err_rs['error_msg'] = '用户编号获取失败';
				echo json_encode($this->err_rs);
				die();
			}else{
				$user_info = $this->apiuser_model->get_user_info($this->app_uid);
				if($user_info){
					unset($user_info['password']);
					unset($user_info['s_password']);
					unset($user_info['openid']);
					$this->rs['user_info']=$user_info;
				}else{
					$this->err_rs['error_msg']='未找到相关用户信息';
					echo json_encode($this->err_rs);
					die();
				}
			}
		}else{
			$this->err_rs['error_msg']='未登陆';
			echo json_encode($this->err_rs);
			die();
		}
	}

	public function index()
	{
		$sum_count = $this->apiuser_model->sum_count($this->app_uid);
		$this->rs['sum_count'] = $sum_count;
		echo json_encode($this->rs);
		die();
	}

	public function information_revise()
	{

		$user_info = $this->rs['user_info'];
		$this->load->model('Apiftontend_model');
		$provinces = $this->Apiftontend_model->get_province();
		$city = $this->Apiftontend_model->get_city($user_info['province_code']);
		$area = $this->Apiftontend_model->get_area($user_info['city_code']);
		$this->rs['user_info']=$user_info;
		$this->rs['provinces_list']=$provinces;
		$this->rs['city_list']=$city;
		$this->rs['area_list']=$area;
		echo json_encode($this->rs);
		die();

	}

	public function save_information_revise(){
		unset($this->rs['user_info']);
		$img = null;
		if($this->input->post('img_input')){
			$img = $this->upload();
		}
		$rs = $this->apiuser_model->save_information_revise($img,$this->app_uid);
		if($rs == 1){
			echo json_encode($this->rs);
			die();
		}else{
			$this->err_rs['error_msg']='保存失败';
			echo json_encode($this->err_rs);
			die();
		}
	}

	public function update_pwd(){
		unset($this->rs['user_info']);
		if(!$this->input->post('new_password')){
			$this->err_rs['error_msg']='新密码不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		$rs = $this->apiuser_model->update_pwd($this->app_uid);
		if($rs == 1){
			echo json_encode($this->rs);
			die();
		}else if($rs == -2){
			$this->err_rs['error_msg']='旧密码填写错误!';
			echo json_encode($this->err_rs);
			die();
		}else{
			$this->err_rs['error_msg']='操作失败!';
			echo json_encode($this->err_rs);
			die();
		}
	}

	public function list_orders(){
		$alltotal = $this->rs['user_info']['total6']+$this->rs['user_info']['total12']+$this->rs['user_info']['total24'];
		unset($this->rs['user_info']);
		$this->rs['all_total']=$alltotal;
		echo json_encode($this->rs);
		die();
	}

	public function list_orders_loaddata(){
		unset($this->rs['user_info']);
		try{
			$page = $this->input->post('page')?$this->input->post('page'):1;
			$data = $this->apiuser_model->list_orders($page,$this->app_uid);
			$this->rs['order_list']=$data['items'];
			echo json_encode($this->rs);
			die();
		}catch (Exception $e) {
			$this->err_rs['error_msg']='操作失败!';
			echo json_encode($this->err_rs);
			die();
		}

	}
}
