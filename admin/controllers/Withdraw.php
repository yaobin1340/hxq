<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Withdraw extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('withdraw_model');
	}

	public function list_withdraw($status,$page=1){

		$data = $this->withdraw_model->list_withdraw($page,$status);
		$base_url = "/admin.php/withdraw/list_withdraw/".$status;
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->assign('status', $status);
		$this->show('withdraw/list_withdraw');
	}


	public function withdraw_detail($id){
		$data = $this->withdraw_model->withdraw_detail($id);
		$this->assign('data', $data);
		$this->show('withdraw/audit_withdraw');
	}

	public function audit_withdraw(){
		$rs = $this->withdraw_model->save_audit_withdraw();
		if($rs == 1){
			$this->show_message('审核成功',site_url('withdraw/list_withdraw/1/1'));
		}else{
			$this->show_message('审核失败');
		}
	}
}