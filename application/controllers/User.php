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
		$this->assign('footer_flag', 4);
	}

	public function index()
	{
		$this->assign('header_name', '用户中心');
		$sum_count = $this->user_model->sum_count();
		$this->assign('sum_count', $sum_count);
		$this->display('user/user_center.html');
	}

    public function user_list()
    {
        $this->display('user/user_list.html');
    }

    public function information_revise()
    {
		$this->assign('header_name', '账户管理');
        $this->load->model('frontend_model');
		$user_info = $this->user_model->get_user_info();
        $provinces = $this->frontend_model->get_province();
        $city = $this->frontend_model->get_city($user_info['province_code']);
        $area = $this->frontend_model->get_area($user_info['city_code']);
        $this->assign('provinces', $provinces);
        $this->assign('city', $city);
        $this->assign('area', $area);
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

	public function update_pwd(){
		if(!$this->input->post('new_password')){
			$this->show_message('新密码不能为空！');
		}
		$rs = $this->user_model->update_pwd();
		if($rs == 1){
			$this->show_message('修改成功',site_url('user'));
		}else if($rs == -2){
			$this->show_message('旧密码填写错误！');
		}else{
			$this->show_message('操作失败');
		}
	}

	public function list_orders($page = 1){
        $this->assign('footer_flag', 2);
		$this->assign('header_name', '我的订单');
		$this->assign('footer_flag', 2);
		$this->assign('s_date', $this->input->post('s_date'));
		$this->assign('e_date', $this->input->post('e_date'));
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
		$this->assign('header_name', '申请提现');
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
			$this->show_message('开户名不能为空！ ');
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
		//$yzm =    123456;
		$text = '您的银行卡提现短信验证码是:'.$yzm;
		$rs = file_get_contents("http://sms-api.luosimao.com/v1/http_get/send/json?key=e3829a670f2c515ab8befa5096dd135c&mobile={$mobile}&message={$text}【三客柚】");
		$obj=json_decode($rs);
		if($obj->error !=0){
			$this->sendsms_curl($mobile,$text);
		}
		$this->session->set_userdata('mobile_code', $yzm);

	}

	public function withdraw_list(){
		//$data = $this->user_model->withdraw_list();
		$this->assign('header_name', '提现日志');
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
		$this->assign('header_name', '资金日志');
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

    public function user_heart($type=3){
		die('数据对接中。。暂时无法打开,预计12.27号15:00恢复,给您带来不便深表歉意。');
		$count = $this->user_model->get_count_heart($type,false);
		$this->assign('header_name', '我的向日葵');
		$this->assign('footer_flag', 3);
		$this->assign('type', $type);
		$this->assign('count', $count);
        $this->display('user/user_heart.html');
    }

	public function user_heart_loaddata($page=1){
		$data = $this->user_model->user_heart_loaddata($page);
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->display('user/user_heart_loaddata.html');
	}

	public function shop_heart($type=3){
		$count = $this->user_model->get_count_heart($type);
		$this->assign('header_name', '我的向日葵');
		$this->assign('footer_flag', 3);
		$this->assign('type', $type);
		$this->assign('count', $count);
		$this->display('user/shop_heart.html');
	}

	public function shop_heart_loaddata($page=1){
		$data = $this->user_model->shop_heart_loaddata($page);
		$this->assign('tab_type', $this->input->post('tab_type'));
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->display('user/shop_heart_loaddata.html');
	}

	public function my_income(){
		$this->assign('header_name', '我的收益');
        $this->display('user/my_income.html');
    }

	public function my_income_loaddata($page=1){
		$data = $this->user_model->my_income_loaddata($page);
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->display('user/my_income_loaddata.html');
	}
    public function my_team($type=1){
		$this->assign('type', $type);
        $this->assign('header_name', '我的团队');
        $this->display('user/my_team.html');
    }
	public function my_team_loaddata($page=1){
		switch ($this->input->post('type')){
			case 1:
				$data = $this->user_model->my_team_user_loaddata($page);
				$this->assign('data', $data);
				$this->assign('page', $page);
				$this->display('user/my_team_loaddata.html');
				break;
			case 2:
				$data = $this->user_model->my_team_shop_loaddata($page);
				$this->assign('data', $data);
				$this->assign('page', $page);
				$this->display('user/my_team_shop_loaddata.html');
				break;
			case 3:
				$data = $this->user_model->my_team_shop2_loaddata($page);
				$this->assign('data', $data);
				$this->assign('page', $page);
				$this->display('user/my_team_shop_loaddata.html');
				break;
			default:
				$data=array();
				echo $data;
		}

	}
	public function test(){
		$data = $this->user_model->my_team_shop_loaddata(1);
		$this->assign('data', $data);
		$this->display('user/my_team_shop_loaddata.html');
	}


    public function pay_money(){
		$data = $this->user_model->pay_money();
		$this->assign('data',$data);
        $this->display('user/pay_money.html');
    }

	public function save_alipay(){
		if($this->session->userdata('mobile_alipay_code') != $this->input->post('yzm')){
			$this->show_message('验证码错误！');
		}
		if((int)$this->input->post('money') < 100){
			$this->show_message('提现金额不能小于要求最小值！');
		}
		if(!trim($this->input->post('alipay_no'))){
			$this->show_message('支付宝账号不能为空！');
		}
		if(!trim($this->input->post('rel_name'))){
			$this->show_message('开户名不能为空！ ');
		}
		$rs = $this->user_model->save_alipay();
		if($rs == 1){
			$this->session->unset_userdata('mobile_alipay_code');
			$this->show_message('提交成功！',site_url('user/withdraw_list'));
		}else if($rs == -1){
			$this->show_message('未登陆！');
		}else if($rs == -2){
			$this->show_message('积分不足！');
		}else{
			$this->show_message('操作失败！');
		}
	}

	public function sendsms_alipay()
	{
		$mobile = $this->input->post('mobile');
		$yzm = rand(100000,999999);
		//$yzm =    123456;
		$text = '您的支付宝提现短信验证码是:'.$yzm;
		$rs = file_get_contents("http://sms-api.luosimao.com/v1/http_get/send/json?key=e3829a670f2c515ab8befa5096dd135c&mobile={$mobile}&message={$text}【三客柚】");
		$obj=json_decode($rs);
		if($obj->error !=0){
			$this->sendsms_curl($mobile,$text);
		}
		$this->session->set_userdata('mobile_alipay_code', $yzm);

	}
}
