<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Commonweal extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('commonweal_model');
	}

	public function list_commonweal($page=1){

		$this->assign('now_page', $page);
		$data = $this->commonweal_model->list_commonweal($page);
		$base_url = "/admin.php/commonweal/list_commonweal";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('commonweal/list_commonweal');
	}

	public function change_status($id){
		$rs = $this->commonweal_model->change_status($id);
		echo $rs;
	}
}