<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "MY_APIcontroller.php";
class Apiftontend extends MY_APIcontroller {

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
	private $app_uid=0;
	public function __construct(){

		parent::__construct();
		$this->load->model('Apiftontend_model');
		$this->token = $this->get_token();
		if($this->token){
			$this->app_uid=$this->get_token_uid($this->token);
		}else{
			$this->app_uid=0;
		}
	}

	public function token_check(){
		if($this->app_uid==0){
			$rs = array(
				'success'=>false,
				'error_msg'=>'token无效'
			);
		}else{
			$rs = array(
				'success'=>true,
				'error_msg'=>''
			);
		}
		echo json_encode($rs);
		die();
	}

	public function index()
	{
		$rs = array(
			'success'=>true,
			'area_name'=>'',
			'area_code'=>'',
			'error_msg'=>''
		);
		$this->load->model('apiuser_model');
		$user_info = $this->apiuser_model->find($this->app_uid);
		if($user_info){
			$area_name = $this->Apiftontend_model->get_area_name($user_info['u_area_code']?$user_info['u_area_code']:null);
			if($area_name){
				$rs['area_name']=$area_name?$area_name['name']:'';
				$rs['area_code']=$user_info['u_area_code'];
			}else{
				$u_area = $this->nearcity($this->input->post('lat'),$this->input->post('lng'));
				$rs['area_name'] = $u_area['name'];
				$rs['area_code'] = $u_area['code'];
			}
		}else{
			$u_area = $this->nearcity($this->input->post('lat'),$this->input->post('lng'));
			$rs['area_name'] = $u_area['name'];
			$rs['area_code'] = $u_area['code'];
		}
		$shop_type = $this->Apiftontend_model->get_shop_type();
		$rs['shop_type_list']=$shop_type;
		echo json_encode($rs);
		die();
	}

	public function index_loaddata(){
		$page = $this->input->post('page')?$this->input->post('page'):1;
		$data = $this->Apiftontend_model->index_loaddata($page,$this->app_uid);
		$rs = array(
			'success'=>true,
			'shop_list'=>$data['items'],
			'error_msg'=>''
		);
		echo json_encode($rs);
		die();
	}

	public function check_login(){
		$user = $this->Apiftontend_model->check_login();
		if($user > 0){
			$token= $this->set_token_uid($user);
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
			$rs = $this->sendsms_curl($mobile,$text);
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
			$rs =$this->sendsms_curl($mobile,$text);
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
			$img = $this->upload();
			$user = $this->Apiftontend_model->save_register($img);
			if($user > 0){
				$token= $this->set_token_uid($user);
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
		$img = $this->upload();
		$dataall = $this->input->post();
		$dataall['time'] = date('Y-m-d H:i:s',time());
		$dataall['file'] = $_FILES;
		$dataall['info'] = $img;
		$open=fopen('/var/yy.txt',"a" );
		fwrite($open,var_export($dataall,true));
		fclose($open);

		echo $img;
	}
	public function get_province(){

		$rs = $this->Apiftontend_model->get_province();
		if($rs){
			$rs = array(
				'success'=>true,
				'province_list'=>$rs,
				'error_msg'=>''
			);
		}else{
			$rs = array(
				'success'=>false,
				'error_msg'=>'未获取到信息'
			);
		}
		echo json_encode($rs);
		die();
	}
	public function get_city(){
		$province_code = $this->input->post('province_code');
		$rs = $this->Apiftontend_model->get_city($province_code);
		if($rs){
			$rs = array(
				'success'=>true,
				'city_list'=>$rs,
				'error_msg'=>''
			);
		}else{
			$rs = array(
				'success'=>false,
				'error_msg'=>'未获取到信息'
			);
		}
		echo json_encode($rs);
		die();
	}
	public function get_area(){
		$city_code = $this->input->post('city_code');
		$rs = $this->Apiftontend_model->get_area($city_code);
		if($rs){
			$rs = array(
				'success'=>true,
				'area_list'=>$rs,
				'error_msg'=>''
			);
		}else{
			$rs = array(
				'success'=>false,
				'error_msg'=>'未获取到信息'
			);
		}
		echo json_encode($rs);
		die();
	}

	public function nearcity($lat=0,$lng=0){
		$default = array(
			'code'=>'310101',
			'name'=>'黄浦区'
		);
		$res = file_get_contents("http://api.map.baidu.com/geocoder?location={$lat},{$lng}&output=xml&key=28bcdd84fae25699606ffad27f8da77b");//百度API
		$xml = simplexml_load_string($res);

		if($xml->status=='OK'){
			$data = $this->Apiftontend_model->nearcity($xml->result->addressComponent->district);
			if($data){
				return $data;
			}else{
				return $default;
			}
		}else{
			return $default;
		}

	}

	public function change_pwd(){
		$re = $this->Apiftontend_model->change_pwd();
		if($re == 1){
			$rs = array(
				'success'=>true,
				'error_msg'=>''
			);
		}else{
			$rs = array(
				'success'=>false,
				'error_msg'=>'修改密码失败!'
			);
		}
		echo json_encode($rs);
		die();
	}

	public function show_information()
	{
		$data = $this->Apiftontend_model->show_information();
		$ysday = $this->Apiftontend_model->yesterday_info();
		$jukuan = $this->Apiftontend_model->jukuan();
		$phangcity= $this->Apiftontend_model->phang_city();
		$phangcompany= $this->Apiftontend_model->phang_company();
		$lminfo = $this->Apiftontend_model->lminfo();
		$rs = array(
			'success'=>true,
			'lminfo'=>$lminfo,
			'phangcity'=>$phangcity,
			'phangcompany'=>$phangcompany,
			'jukuan'=>$jukuan,
			'data'=>$data,
			'ysday'=>$ysday,
			'error_msg'=>''
		);
		echo json_encode($rs);
		die();
	}

	public function shop_detail(){
		if(!$this->input->post('shop_id')){
			$rs = array(
				'success'=>false,
				'error_msg'=>'商铺编号不能为空!'
			);
			echo json_encode($rs);
			die();
		}
		$shop_id = $this->input->post('shop_id');
		$data = $this->Apiftontend_model->shop_details($shop_id);
		if($data){
			$rs = array(
				'success'=>true,
				'error_msg'=>'',
				'shop_detail'=>$data
			);
		}else{
			$rs = array(
				'success'=>false,
				'error_msg'=>'未找到商铺信息!'
			);
		}
		echo json_encode($rs);
		die();
	}

	public function get_shop_type(){
		$rs = array(
			'success'=>true,
			'error_msg'=>''
		);
		$shop_type = $this->Apiftontend_model->get_shop_type();
		$rs['shop_type_list']=$shop_type;
		echo json_encode($rs);
		die();
	}

	public function get_turns_imgs(){
		$img_list = array();
		$img_list[]='statics/images/banner.jpg';
		$img_list[]='statics/images/banner1.jpg';
		$img_list[]='statics/images/banner2.jpg';
		$rs = array(
			'success'=>true,
			'img_list'=>$img_list,
			'error_msg'=>''
		);
		echo json_encode($rs);
		die();

	}
}
