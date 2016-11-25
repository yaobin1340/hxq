<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends MY_Controller {

	/**
	 * Index Page for this controller.
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('index_model');
	}

	public function index()
	{
		$count_info = $this->index_model->get_index_count();
		$this->assign('count_info', $count_info);
		$this->display('layout/index.html');

	}
	
	public function main(){
		$sett_info = $this->index_model->get_sett_info();
		//var_dump($sett_info);
		$this->assign('sett_info', $sett_info);
		$this->show('index');
	}


}
