<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {

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
		if(!$this->session->userdata('uid')){
			$this->show_message('请先登陆~',site_url('/frontend/login'));
		}
		$this->load->model('user_model');
		$user_info = $this->user_model->get_user_info();
		$this->assign('user_info', $user_info);
	}

	public function index()
	{
		$this->display('user/user_center.html');
	}
    public function test()
    {
        $this->display('user/test.html');
    }
    public function user_list()
    {
        $this->display('user/user_list.html');
    }

    public function information_revise()
    {
        $this->display('user/information_revise.html');
    }
}
