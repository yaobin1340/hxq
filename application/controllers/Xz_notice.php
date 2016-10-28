<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Xz_notice extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('executive_model');
	}
	
	public function list_notice($page=1){
		$data = $this->executive_model->list_notice($page);
		$base_url = "/xz_notice/list_notice";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('executive/list_notice');
	}
	
	public function add_notice(){
		$this->show('executive/add_notice');
	}
	
	public function show_notice($id){
		$data = $this->executive_model->get_notice($id);
		$this->assign('data', $data);
		$this->show('executive/edit_notice');
	}
	
	public function save_notice(){
		$rs = $this->executive_model->save_notice();
		if($rs == 1){
			$this->show_message('保存成功',site_url('xz_notice/list_notice'));
		}else{
			$this->show_message('保存失败');
		}
	}
	
//	public function del_company($id){
//		$rs = $this->rule_model->del_company($id);
//		if($rs == 1){
//			$this->show_message('删除成功',site_url('rule_company/list_company'));
//		}else{
//			$this->show_message('删除失败');
//		}
//	}
	
//	public function show_company($id){
//		$data = $this->rule_model->get_company($id);
//		$this->assign('data', $data);
//		$company_all = $this->rule_model->list_company_all();
//		$this->assign('company_all', $company_all);
//		$this->show('rule/edit_company');
//	}
//
//	public function edit_company($id){
//		$this->show_company($id);
//	}
	
}