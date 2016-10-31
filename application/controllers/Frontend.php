<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Frontend extends MY_Controller {

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
		$this->load->model('main_model');
	}

	//
	public function index()
	{
		$this->display('frontend/index.html');
	}
	
	public function main(){
		
//		$bulletins = $this->main_model->display_bulletin();
//		foreach ($bulletins as &$b) {
//			$year = date('Y', strtotime($b['cdate']));
//			$b['bulletin_num'] = $year . '-' . str_pad($b['num'], 3, '0', STR_PAD_LEFT);
//		}
//		$this->assign('bulletins', $bulletins);
		
//		$bulletin_checks = $this->main_model->display_bulletin_check();
//		$this->assign('bulletin_checks', $bulletin_checks);
		
		$this->show('index');
	}


}
