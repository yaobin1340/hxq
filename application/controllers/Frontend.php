<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Frontend extends MY_Controller {

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
		$this->load->model('frontend_model');
	}

	//
	public function index()
	{
		die(BASEPATH);
		$this->display('frontend/index.html');
	}
	
	public function register(){
		if($this->input->post()){
			if($this->input->post('yzm') != $this->session->userdata('yzm')){
				$this->show_message('验证码错误');
			}
			$this->session->set_userdata('mobile',$this->input->post('mobile'));
			$provinces = $this->frontend_model->get_province();
			$this->assign('provinces', $provinces);
			$this->display('frontend/register2.html');
		}else{
			$this->display('frontend/register.html');
		}
	}

	public function save_register(){
		$rs = $this->frontend_model->save_register();
		if($rs == 1){
			$this->show_message('注册成功');
		}else{
			$this->show_message('注册失败');
		}
	}

	public function get_yzm($mobile){
		$yzm = rand(100000,999999);
		$text = '您的短信验证码是:'.$yzm;
		$this->session->set_userdata('yzm',$yzm);
		$rs = file_get_contents("http://sms-api.luosimao.com/v1/http_get/send/json?key=e3829a670f2c515ab8befa5096dd135c&mobile={$mobile}&message={$text}【拉拉秀】");
		echo $rs;
	}

	public function get_city($province_code){
		$rs = $this->frontend_model->get_city($province_code);
		echo json_encode($rs);
	}

}
