<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Goods extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('goods_model');
	}

	public function list_goods($page=1){
		$data = $this->goods_model->list_goods($page);
		$base_url = "/admin.php/goods/list_goods/";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('goods/list_goods');
	}

	public function save_good(){
		echo $this->goods_model->save_good();
	}

	public function get_super_id($id){
		$rs =  $this->super_id_model->get_super_id($id);
		echo json_encode($rs);
	}

	public function delete_super_id($id){
		$rs =  $this->super_id_model->delete_super_id($id);
		echo json_encode($rs);
	}
}