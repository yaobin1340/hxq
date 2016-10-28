<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Common extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('common_model');
	}
	
	public function get_notince_count(){
		$num = $this->common_model->get_notice_count();
		echo $num;
	}
	
	public function list_notice($page=1){
		$data = $this->common_model->list_notice($page);
		$base_url = "/common/list_notice";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('common/list_notice');
	}
	
	public function show_notice($id){
		$data = $this->common_model->get_notice($id);
		if($data == '-1'){
			$this->show_message('非法操作');
		}
		$this->assign('data', $data);
		$this->show('common/show_notice');
	}
	
	public function get_user_list($dept_id){
		$users = $this->common_model->get_user_list($dept_id);
		echo json_encode($users);
	}
}