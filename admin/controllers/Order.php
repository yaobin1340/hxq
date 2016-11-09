<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('order_model');
	}

	public function list_orders($page=1){
		$data = $this->order_model->list_orders($page);
		$base_url = "/admin.php/shop/list_shop_type";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('order/list_orders');
	}

	public function save_order(){
		$rs = $this->order_model->save_order();
		if($rs == 1){
			$this->show_message('保存成功',site_url('shop/list_orders'));
		}else{
			$this->show_message('保存失败');
		}
	}

	public function order_detail($id){
		$data = $this->order_model->get_order_detail($id);
		$this->assign('data', $data);
		$this->show('order/order_detail');
	}


}