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
				header('HTTP/1.1 401 Unauthorized');
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
//					header('status: 401');
					header('HTTP/1.1 401 Unauthorized');
					$this->err_rs['error_msg']='未找到相关用户信息';
					echo json_encode($this->err_rs);
					die();
				}
			}
		}else{
//			header('status: 401');
			header('HTTP/1.1 401 Unauthorized');
			$this->err_rs['error_msg']='未登陆';
			echo json_encode($this->err_rs);
			die();
		}
	}

	public function index()
	{
		$shop_flag = $this->apiuser_model->get_shop_flag($this->app_uid);
		$this->rs['shop_flag'] = $shop_flag;
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
		if($data){
			$this->rs['withdraw_info']=$data;
		}else{
			$this->rs['withdraw_info']=(object)array();
		}

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

	public function heart_count(){
		unset($this->rs['user_info']);
		$user_count6 = $this->apiuser_model->get_count_heart(1,false,$this->app_uid);
		$user_count12 = $this->apiuser_model->get_count_heart(2,false,$this->app_uid);
		$user_count24 = $this->apiuser_model->get_count_heart(3,false,$this->app_uid);
		$shop_count6 = $this->apiuser_model->get_count_heart(1,true,$this->app_uid);
		$shop_count12 = $this->apiuser_model->get_count_heart(2,true,$this->app_uid);
		$shop_count24 = $this->apiuser_model->get_count_heart(3,true,$this->app_uid);
		$this->rs['user_count6']=$user_count6;
		$this->rs['user_count12']=$user_count12;
		$this->rs['user_count24']=$user_count24;
		$this->rs['shop_count6']=$shop_count6;
		$this->rs['shop_count12']=$shop_count12;
		$this->rs['shop_count24']=$shop_count24;
		echo json_encode($this->rs);
		die();
	}

	public function user_heart_loaddata(){
		unset($this->rs['user_info']);
		try{
			if(!$this->input->post('tab_type')){
				$this->err_rs['error_msg']='获取向日葵系列类型参数不能为空!';
				echo json_encode($this->err_rs);
				die();
			}
			$page = $this->input->post('page')?$this->input->post('page'):1;
			$data = $this->apiuser_model->user_heart_loaddata($page,$this->app_uid);
			$this->rs['user_heart_list']=$data['items'];
			echo json_encode($this->rs);
			die();
		}catch (Exception $e) {
			$this->err_rs['error_msg']='操作失败!';
			echo json_encode($this->err_rs);
			die();
		}

	}

	public function shop_heart_loaddata(){
		unset($this->rs['user_info']);
		try{
			if(!$this->input->post('tab_type')){
				$this->err_rs['error_msg']='获取向日葵系列类型参数不能为空!';
				echo json_encode($this->err_rs);
				die();
			}
			$page = $this->input->post('page')?$this->input->post('page'):1;
			$data = $this->apiuser_model->shop_heart_loaddata($page,$this->app_uid);
			$this->rs['shop_heart_list']=$data['items'];
			echo json_encode($this->rs);
			die();
		}catch (Exception $e) {
			$this->err_rs['error_msg']='操作失败!';
			echo json_encode($this->err_rs);
			die();
		}

	}

	public function my_income_loaddata(){
		unset($this->rs['user_info']);
		$page = $this->input->post('page')?$this->input->post('page'):1;
		$data = $this->apiuser_model->my_income_loaddata($this->app_uid,$page);
		$this->rs['my_income_list']=$data['items'];
		echo json_encode($this->rs);
		die();
	}

	public function register_shop(){
		$this->load->model('apishop_model');
		$shop_info = $this->apishop_model->get_shop_info($this->app_uid,false);
		if($shop_info){
			$this->rs['shop_info']=$shop_info;
			echo json_encode($this->rs);
			die();
		}else{
			$this->rs['shop_info']=(object)array();
			echo json_encode($this->rs);
			die();
		}
	}

	public function save_register_shop(){
		unset($this->rs['user_info']);
		if(!trim($this->input->post('shop_name'))){
			$this->err_rs['error_msg']='商铺名称不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('parent_flag'))){
			$this->err_rs['error_msg']='邀请人不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('type'))){
			$this->err_rs['error_msg']='商铺类型不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('province_code'))){
			$this->err_rs['error_msg']='省份不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('city_code'))){
			$this->err_rs['error_msg']='城市不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('area_code'))){
			$this->err_rs['error_msg']='区域不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('address'))){
			$this->err_rs['error_msg']='地址不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('person'))){
			$this->err_rs['error_msg']='联系人不能为空!';
			echo json_encode($this->err_rs);
			die();
		}

		if(!trim($this->input->post('phone'))){
			$this->err_rs['error_msg']='联系电话不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('business_time'))){
			$this->err_rs['error_msg']='营业时间不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('lng')) || !trim($this->input->post('lat'))){
			$this->err_rs['error_msg']='经纬度不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('percent'))){
			$this->err_rs['error_msg']='分销类型不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		if(!trim($this->input->post('desc'))){
			$this->err_rs['error_msg']='商铺介绍不能为空!';
			echo json_encode($this->err_rs);
			die();
		}
		$img = $this->upload('logo');
		$license = $this->upload('license','license');
		$cns1 = $this->upload('cns','cns1');
//        $cns2 = $this->upload('cns','cns2');
		$sfz1 = $this->upload('sfz','sfz1');
//        $sfz2 = $this->upload('sfz','sfz2');
		$imgs = array(
			'logo'=>$img,
			'license'=>$license,
			'cns1'=>$cns1,
//            'cns2'=>$cns2,
			'sfz1'=>$sfz1,
//            'sfz2'=>$sfz2,
		);
		$rs = $this->apiuser_model->save_register_shop($this->app_uid,$imgs);
		if($rs == 1){
			echo json_encode($this->rs);
			die();
		}else if($rs == -2){
			$this->err_rs['error_msg']='推荐人不可为自己!';
			echo json_encode($this->err_rs);
			die();
		}else{
			$this->err_rs['error_msg']='申请失败!';
			echo json_encode($this->err_rs);
			die();
		}
	}
}
