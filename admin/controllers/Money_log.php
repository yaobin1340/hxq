<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Money_log extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('money_log_model');
	}

	public function list_money_log($page=1){
		$type_list = $this->money_log_model->get_type_list();
		$this->assign('type_list', $type_list);
		$data = $this->money_log_model->list_money_log($page);
		$base_url = "/admin.php/money_log/list_money_log/";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('money_log/list_money_log');
	}

}