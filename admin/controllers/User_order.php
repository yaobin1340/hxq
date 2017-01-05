<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_order extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('User_order_model','Uorder');
	}

	public function list_orders($page=1){
		$data = $this->Uorder->list_orders($page);
		$base_url = "/admin.php/user_order/list_orders/";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->show('user_order/list_orders');
	}

	public function save_order(){
		$rs = $this->order_model->save_order();
		if($rs == 1){
			$this->show_message('保存成功',site_url('order/list_orders'));
		}elseif($rs == -2){
			$this->show_message('请等待上一日向日葵核算后再审核');
		}else{
			$this->show_message('保存失败');
		}
	}

	public function order_detail($id,$page=1){
		$data = $this->Uorder->get_order_detail($id);
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->show('user_order/order_detail');
	}


}