<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('order_model');
	}

	public function list_orders($status=2,$page=1){
		$data = $this->order_model->list_orders($page,$status);
		$base_url = "/admin.php/Order/list_orders/".$status;
		$pager = $this->pagination->getPageLink_by4($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->show('order/list_orders');
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

	public function order_detail($id,$status=2,$page=1){
		$data = $this->order_model->get_order_detail($id);
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->assign('status', $status);
		$this->show('order/order_detail');
	}


}