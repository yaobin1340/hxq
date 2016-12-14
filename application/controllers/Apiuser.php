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
		$img = $this->upload();
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

	public function withdraw(){
		$data = $this->apiuser_model->withdraw($this->app_uid);
		$this->rs['withdraw_info']=$data;
		echo json_encode($this->rs);
		die();
	}

	public function save_withdraw(){
		unset($this->rs['user_info']);
		if((int)$this->input->post('money') < 100){
			$this->err_rs['error_msg']='提现金额不能小于要求最小值！';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('bank'))){
			$this->err_rs['error_msg']='开户银行不能为空！';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('bank_no'))){
			$this->err_rs['error_msg']='银行账号不能为空！';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('bank_branch'))){
			$this->err_rs['error_msg']='具体支行不能为空！';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('rel_name'))){
			$this->err_rs['error_msg']='开户名不能为空！';
			echo json_encode($this->err_rs);
			die();
		}
		$rs = $this->apiuser_model->save_withdraw($this->app_uid);
		if($rs == 1){
			echo json_encode($this->rs);
			die();
		}else if($rs == -1){
			$this->err_rs['error_msg']='未登陆！';
			echo json_encode($this->err_rs);
			die();
		}else if($rs == -2){
			$this->err_rs['error_msg']='积分不足！';
			echo json_encode($this->err_rs);
			die();
		}else{
			$this->err_rs['error_msg']='操作失败！';
			echo json_encode($this->err_rs);
			die();
		}
	}

	public function sendsms()
	{
		unset($this->rs['user_info']);
		$mobile = $this->input->post('mobile');
		$yzm = rand(100000,999999);
		//$yzm     = 123456;
		$text = '您的短信验证码是:'.$yzm;
		$rs = file_get_contents("http://sms-api.luosimao.com/v1/http_get/send/json?key=e3829a670f2c515ab8befa5096dd135c&mobile={$mobile}&message={$text}【三客柚】");
		$obj=json_decode($rs);
		if($obj->error !=0){
			$rs=$this->sendsms_curl($mobile,$text);
		}
		$obj=json_decode($rs);
		if($obj->error !=0){
			$this->err_rs['error_msg']='获取验证码失败！';
			echo json_encode($this->err_rs);
			die();
		}
		$this->rs['yzm']=$yzm;
		echo json_encode($this->rs);
		die();
	}

	public function list_withdraw_loaddata(){
		unset($this->rs['user_info']);
		$page = $this->input->post('page')?$this->input->post('page'):1;
		$data = $this->apiuser_model->list_withdraw_loaddata($page,$this->app_uid);
		$this->rs['withdraw_list']=$data['items'];
		echo json_encode($this->rs);
		die();
	}

	public function money_log_list_loaddata(){
		unset($this->rs['user_info']);
		$page = $this->input->post('page')?$this->input->post('page'):1;
		$data = $this->apiuser_model->money_log_list_loaddata($page,$this->app_uid);
		$this->rs['money_log_list']=$data['items'];
		echo json_encode($this->rs);
		die();
	}

	public function my_team_user(){
		unset($this->rs['user_info']);
		$page = $this->input->post('page')?$this->input->post('page'):1;
		$data = $this->apiuser_model->my_team_user_loaddata($page,$this->app_uid);
		$this->rs['my_team_list']=$data['items'];
		echo json_encode($this->rs);
		die();
	}

	public function my_team_shop(){
		unset($this->rs['user_info']);
		$page = $this->input->post('page')?$this->input->post('page'):1;
		$data = $this->apiuser_model->my_team_shop_loaddata($page,$this->app_uid);
		$this->rs['my_team_list']=$data['items'];
		echo json_encode($this->rs);
		die();
	}

	public function my_team_shop2(){
		unset($this->rs['user_info']);
		$page = $this->input->post('page')?$this->input->post('page'):1;
		$data = $this->apiuser_model->my_team_shop2_loaddata($page,$this->app_uid);
		$this->rs['my_team_list']=$data['items'];
		echo json_encode($this->rs);
		die();
	}
}
