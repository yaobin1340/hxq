<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('user_model');
	}

	public function change_status($id,$status){
		$rs = $this->user_model->change_status($id,$status);
		echo $rs;
	}
	
	public function list_users($page=1){
		$data = $this->user_model->list_users($page);
		$base_url = "/admin.php/user/list_users";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('user/list_users');
	}


	public function user_detail($id){
		$data = $this->user_model->get_user_detail($id);
		$this->assign('data', $data);
		$this->show('user/user_detail');
	}

	public function user_upgrade(){
		$id = $this->input->post('id');
		$rs = $this->user_model->user_upgrade($id);
		$this->show_message('升级成功~',site_url("/user/user_detail/$id"));
	}



}