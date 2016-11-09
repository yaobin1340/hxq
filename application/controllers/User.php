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
        $this->load->model('frontend_model');
        $provinces = $this->frontend_model->get_province();
        $this->assign('provinces', $provinces);
        $this->display('user/information_revise.html');
    }

	public function save_information_revise(){
		$img = null;
		if($this->input->post('img_input')){
			$img = $this->upload();
		}
		$rs = $this->user_model->save_information_revise($img);
		if($rs == 1){
			$this->show_message('修改成功',site_url('user'));
		}else{
			$this->show_message('操作失败');
		}
	}

	public function list_orders($page = 1){
        $data = $this->user_model->list_orders($page);
        $this->assign('data', $data);
		$this->display('user/list_orders.html');
	}

    public function list_orders_loaddata($page = 1){
        $data = $this->user_model->list_orders($page);
        $this->assign('data', $data);
        $this->assign('page', $page);
        $this->display('user/list_orders_loaddata.html');
    }

    public function get_name_by_keywords($keywords){
        echo $this->user_model->get_name_by_keywords($keywords);
    }

    public function del_order($id){
        echo $this->user_model->del_order($id);
    }

    public function list_order_audit(){
        $this->assign('s_date', $this->input->post('s_date'));
        $this->assign('e_date', $this->input->post('e_date'));
        $this->display('user/list_order_audit.html');
    }

    public function list_order_audit_loaddata($page = 1){
        $data = $this->user_model->list_order_audit($page);
        $this->assign('data', $data);
        $this->assign('page', $page);
        $this->display('user/list_order_audit_loaddata.html');
    }

}
