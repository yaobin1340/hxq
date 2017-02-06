<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends MY_Controller {

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
	}

	public function index()
	{
		$count_info = $this->index_model->get_index_count();
		$this->assign('count_info', $count_info);
		$this->display('layout/index.html');

	}

	public function main(){
		$index_data = $this->index_model->index_data();
		$sett_info = $this->index_model->get_sett_info();
		$sy = $this->index_model->get_sy();
		$ywy = $this->index_model->ywy();
		$shop2_yey = $this->index_model->shop2_yey();
		$shop_month_yey = $this->index_model->shop_month_yey();
		//var_dump(date("Y-m"));
		$this->assign('index_data', $index_data);
		$this->assign('ywy', $ywy);
		$this->assign('shop2_yey', $shop2_yey);
		$this->assign('shop_month_yey', $shop_month_yey);
		$this->assign('yesday', date("Y-m-d",strtotime("-1 day")));
		$this->assign('now_month', date("Y-m"));
		$this->assign('sett_info', $sett_info);
		$this->assign('sy', $sy);
		$this->show('index');
	}

	public function update_password(){
		echo $this->index_model->update_password();
		die;
	}

	public function check_pass($pass) {
		$user_info = $this->session->userdata('user_info');
		echo $user_info['pwd'] == sha1($pass) ? 1 : 0;
		die;
	}
}
