<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "MY_APIcontroller.php";
class Apishop extends MY_APIcontroller {

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
	private $shop_id;
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
		$this->load->model('apishop_model');
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
					$shop_info = $this->apishop_model->get_shop_info($this->app_uid);
					if(!$shop_info){
						$this->err_rs['error_msg']='不是商家,需先申请入驻';
						echo json_encode($this->err_rs);
						die();
					}else{
						$this->shop_id = $shop_info['id'];
						$this->rs['shop_info']=$shop_info;
					}
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

	public function shop_info(){
		$sum_count_shop = $this->apishop_model->sum_count_shop($this->shop_id);
		$this->rs['sum_count_shop']=$sum_count_shop;
		echo json_encode($this->rs);
		die();
	}

	public function list_orders_loaddata(){
		unset($this->rs['user_info']);
		unset($this->rs['shop_info']);
		$page = $this->input->post('page')?$this->input->post('page'):1;
		$data = $this->apishop_model->list_orders($this->app_uid,$page);
		$this->rs['list_orders']=$data['items'];
		echo json_encode($this->rs);
		die();
	}
}
