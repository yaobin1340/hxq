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
		echo 1;
	}


	

}