<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('shop_model');
	}

	public function list_shop_type($page=1){
		$data = $this->shop_model->list_shop_type($page);
		$base_url = "/admin.php/shop/list_shop_type";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('shop/list_shop_type');
	}

	public function add_shop_type(){
		$this->show('shop/add_shop_type');
	}


	public function save_shop_type(){
		$rs = $this->shop_model->save_shop_type();
		if($rs == 1){
			$this->show_message('保存成功',site_url('shop/list_shop_type'));
		}else{
			$this->show_message('保存失败');
		}
	}

	public function change_status($id,$status){
		$rs = $this->shop_model->change_status($id,$status);
		if($rs == 1){
			$this->show_message('操作成功',site_url('shop/list_shop_type'));
		}else{
			$this->show_message('操作失败');
		}
	}
	
	public function list_shop_audit($page=1){
		$data = $this->shop_model->list_shop_audit($page);
		$base_url = "/admin.php/shop/list_shop_audit";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('shop/list_shop_audit');
	}

	public function audit_shop($id){
		$data = $this->shop_model->get_audit_shop($id);
		$this->assign('data', $data);
		$this->show('shop/audit_shop');
	}

	public function save_audit_shop(){
		$rs = $this->shop_model->save_audit_shop();
		if($rs == 1){
			$this->show_message('审核成功',site_url('shop/list_shop_audit'));
		}else{
			$this->show_message('审核失败');
		}
	}

	public function list_shop($page=1){
		$data = $this->shop_model->list_shop($page);
		$base_url = "/admin.php/shop/list_shop";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('shop/list_shop');
	}

	public function shop_detail($id){
		$data = $this->shop_model->get_shop_detail($id);
		$this->assign('data', $data);
		$this->show('shop/shop_detail');
	}

	public function non_use_shop($id,$status){
		$rs = $this->shop_model->non_use_shop($id,$status);
		echo $rs;
	}
	

}