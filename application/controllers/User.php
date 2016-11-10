<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {

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
		$this->load->model('user_model');
		$user_info = $this->user_model->get_user_info();
		$this->assign('user_info', $user_info);
	}

	public function index()
	{
		$this->display('user/user_center.html');
	}
    public function test()
    {
        $this->display('user/test.html');
    }
    public function user_list()
    {
        $this->display('user/user_list.html');
    }

    public function information_revise()
    {
        $this->load->model('frontend_model');
        $provinces = $this->frontend_model->get_province();
        $this->assign('provinces', $provinces);
        $this->display('user/information_revise.html');
    }

	public function save_information_revise(){
		$img = null;
		if($this->input->post('img_input')){
			$img = $this->upload();
		}
		$rs = $this->user_model->save_information_revise($img);
		if($rs == 1){
			$this->show_message('修改成功',site_url('user'));
		}else{
			$this->show_message('操作失败');
		}
	}

	public function list_orders($page = 1){
        $data = $this->user_model->list_orders($page);
        $this->assign('data', $data);
		$this->display('user/list_orders.html');
	}

    public function list_orders_loaddata($page = 1){
        $data = $this->user_model->list_orders($page);
        $this->assign('data', $data);
        $this->assign('page', $page);
        $this->display('user/list_orders_loaddata.html');
    }

    public function get_name_by_keywords($keywords){
        echo $this->user_model->get_name_by_keywords($keywords);
    }

    public function del_order($id){
        echo $this->user_model->del_order($id);
    }

    public function list_order_audit(){
        $this->assign('s_date', $this->input->post('s_date'));
        $this->assign('e_date', $this->input->post('e_date'));
        $this->display('user/list_order_audit.html');
    }

    public function list_order_audit_loaddata($page = 1){
        $data = $this->user_model->list_order_audit($page);
        $this->assign('data', $data);
        $this->assign('page', $page);
        $this->display('user/list_order_audit_loaddata.html');
    }
	public function withdraw(){
		$data = $this->user_model->withdraw();
		$this->assign('data',$data);
		$this->display('user/withdraw.html');
	}

	public function save_withdraw(){
		if($this->session->userdata('mobile_code') != $this->input->post('yzm')){
			$this->show_message('验证码错误！');
		}
		if((int)$this->input->post('money') < 100){
			$this->show_message('提现金额不能小于要求最小值！');
		}
		if(!trim($this->input->post('bank'))){
			$this->show_message('开户银行不能为空！');
		}
		if(!trim($this->input->post('bank_no'))){
			$this->show_message('银行账号不能为空！');
		}
		if(!trim($this->input->post('bank_branch'))){
			$this->show_message('具体支行不能为空！');
		}
		if(!trim($this->input->post('rel_name'))){
			$this->show_message('开户名不能为空！');
		}
		$rs = $this->user_model->save_withdraw();
		if($rs == 1){
			$this->session->unset_userdata('mobile_code');
			$this->show_message('提交成功！',site_url('user/withdraw_list'));
		}else if($rs == -1){
			$this->show_message('未登陆！');
		}else if($rs == -2){
			$this->show_message('积分不足！');
		}else{
			$this->show_message('操作失败！');
		}
	}

	public function sendsms()
	{
		$mobile = $this->input->post('mobile');
		$yzm = rand(100000,999999);
		//$yzm = 123456;
		$text = '您的短信验证码是:'.$yzm;
		$rs = file_get_contents("http://sms-api.luosimao.com/v1/http_get/send/json?key=e3829a670f2c515ab8befa5096dd135c&mobile={$mobile}&message={$text}【拉拉秀】");
		$obj=json_decode($rs);
		if($obj->error !=0){
			$this->sendsms_curl($mobile,$text);
		}
		$this->session->set_userdata('mobile_code', $yzm);

	}

	public function sendsms_curl($moblie,$text){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");

		curl_setopt($ch, CURLOPT_HTTP_VERSION  , CURL_HTTP_VERSION_1_0 );
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD  , 'api:key-e3829a670f2c515ab8befa5096dd135c');

		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $moblie,'message' => "{$text}【拉拉秀】"));

		$res = curl_exec( $ch );
		curl_close( $ch );
	}

	public function withdraw_list(){
		//$data = $this->user_model->withdraw_list();
		$this->display('user/withdraw_list.html');
	}

	public function list_withdraw_loaddata($page = 1){
		$data = $this->user_model->list_withdraw_loaddata($page);
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->display('user/withdraw_list_loaddata.html');
	}

	public function money_log_list(){
		//$data = $this->user_model->withdraw_list();
		$this->assign('s_date', $this->input->post('s_date'));
		$this->assign('e_date', $this->input->post('e_date'));
		$this->display('user/money_log_list.html');
	}

	public function money_log_list_loaddata($page = 1){
		$data = $this->user_model->money_log_list_loaddata($page);
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->display('user/money_log_list_loaddata.html');
	}
}
