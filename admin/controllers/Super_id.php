<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Super_id extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('super_id_model');
	}

	public function list_super_id($page=1){
		$data = $this->super_id_model->list_super_id($page);
		$base_url = "/admin.php/super_id/list_super_id/";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('super_id/list_super_id');
	}

	public function save_super_id(){
		echo $this->super_id_model->save_super_id();
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