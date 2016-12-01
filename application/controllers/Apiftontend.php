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

	public function get_yzm(){
		if(!$this->input->post('mobile')){
			$rs = array(
				'success'=>false,
				'error_msg'=>'必须提供手机号码'
			);
			echo json_encode($rs);
			die();
		}
		$mobile = $this->input->post('mobile');
		if($this->Apiftontend_model->check_mobile($mobile)){
			$rs = array(
				'success'=>false,
				'error_msg'=>'手机已被注册'
			);
			echo json_encode($rs);
			die();
		}
		$yzm = rand(100000,999999);
		$text = '您的短信验证码是:'.$yzm;
		$rs = file_get_contents("http://sms-api.luosimao.com/v1/http_get/send/json?key=e3829a670f2c515ab8befa5096dd135c&mobile={$mobile}&message={$text}【三客柚】");
		$obj=json_decode($rs);
		if($obj->error !=0){
			$rs = $this->apifun->sendsms_curl($mobile,$text);
		}
		$obj=json_decode($rs);
		if($obj->error !=0){
			$rs = array(
				'success'=>false,
				'error_msg'=>'获取验证码失败'
			);
			echo json_encode($rs);
			die();
		}
		$res = array(
			'success'=>true,
			'yzm'=>$yzm,
			'error_msg'=>''
		);
		echo json_encode($res);
		die();
	}

	public function get_yzm_forget(){
		if(!$this->input->post('mobile')){
			$rs = array(
				'success'=>false,
				'error_msg'=>'必须提供手机号码'
			);
			echo json_encode($rs);
			die();
		}
		$mobile = $this->input->post('mobile');
		if(!$this->Apiftontend_model->check_mobile($mobile)){
			$rs = array(
				'success'=>false,
				'error_msg'=>'手机号码未被注册'
			);
			echo json_encode($rs);
			die();
		}
		$yzm = rand(100000,999999);
		$text = '您的短信验证码是:'.$yzm;
		$rs = file_get_contents("http://sms-api.luosimao.com/v1/http_get/send/json?key=e3829a670f2c515ab8befa5096dd135c&mobile={$mobile}&message={$text}【三客柚】");
		$obj=json_decode($rs);
		if($obj->error !=0){
			$rs =$this->apifun->sendsms_curl($mobile,$text);
		}
		$obj=json_decode($rs);
		if($obj->error !=0){
			$rs = array(
				'success'=>false,
				'error_msg'=>'获取验证码失败'
			);
			echo json_encode($rs);
			die();
		}
		$res = array(
			'success'=>true,
			'yzm'=>$yzm,
			'error_msg'=>''
		);
		echo json_encode($res);
		die();
	}

	public function save_register(){
		//try{
			$img = $this->apifun->upload();
			$user = $this->Apiftontend_model->save_register($img);
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
					'error_msg'=>'注册失败'
				);
			}
			echo json_encode($rs);
			die();
		/*}catch(Exception $e) {
			$rs = array(
				'success' => false,
				'error_msg'=>'Api失败!'
			);
			echo json_encode($rs);
			die();
		}*/

	}

	public function get_naid_by_keywords(){
		if(!$this->input->post('keywords')){
			$rs = array(
				'success'=>false,
				'error_msg'=>'必须提供推荐人信息'
			);
			echo json_encode($rs);
			die();
		}
		$keywords = $this->input->post('keywords');
		$res = $this->Apiftontend_model->get_naid_by_keywords($keywords);
		if($res){
			$rs = array(
				'success'=>true,
				'name'=>$res['rel_name'],
				'id'=>$res['id'],
				'error_msg'=>''
			);
		}else{
			$rs = array(
				'success'=>false,
				'error_msg'=>'未找到推荐人信息'
			);
		}
		echo json_encode($rs);
		die();
	}

	public function test(){
		$img = $this->apifun->upload();
		echo $img;
	}

}
