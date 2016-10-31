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
		$this->load->model('index_model');
		$this->load->model('main_model');
	}

	//
	public function index()
	{
		$this->display('frontend/index.html');
	}
	
	public function register(){
		if($this->input->post()){
			var_dump($this->input->post('mobile'));
		}else{
			$this->display('frontend/register.html');
		}
	}

	public function get_yzm($mobile){
		$yzm = rand(100000,999999);
		$text = '您的短信验证码是:'.$yzm;
		$this->session->set_userdata('yzm',$yzm);
		$rs = file_get_contents("http://sms-api.luosimao.com/v1/http_get/send/json?key=e3829a670f2c515ab8befa5096dd135c&mobile={$mobile}&message={$text}【拉拉秀】");
		echo $rs;
	}

}
