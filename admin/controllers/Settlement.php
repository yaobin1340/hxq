<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settlement extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('settlement_model');
	}

	public function list_settlements($page=1){
		$data = $this->settlement_model->list_settlements($page);
		$base_url = "/admin.php/settlement/list_settlements";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('settlement/list_settlements');
	}

	//每日结算
	public function settlement(){
		$rs = $this->settlement_model->settlement();
		echo $rs;
	}

	public function settlement_detail($id){
		$data = $this->settlement_model->settlement_detail($id);
		$this->assign('data', $data);
		$this->show('settlement/settlement_detail');
	}

	public function audit_settlement($id){
		$rs = $this->settlement_model->audit_settlement($id);
		echo $rs;
	}

	public function change_settlement($id){
		$data = $this->settlement_model->settlement_detail($id);
		$this->assign('data', $data);
		$this->show('settlement/change_settlement');
	}

	public function save_change_settlement(){
		$rs = $this->settlement_model->save_change_settlement();
		if($rs == 1){
			$this->show_message('保存成功',site_url('settlement/list_settlements'));
		}else{
			$this->show_message('保存失败');
		}
	}

	public function delete_settlement($id){
		$rs = $this->settlement_model->delete_settlement($id);
		echo $rs;
	}


	

}