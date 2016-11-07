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

    public function save_order(){
        $this->load->model('order_model');
        $this->load->model('order_list_model');
        //验证
        $user_flag = $this->input->post('user_flag');
        $user = $this->user_model->get_user($user_flag);
        if(!$user) $this->show_message('用户不存在~');

        $conditionFields = array();
        $conditionFields['status'] = 1;
        $dbOrder = $this->order_model->queryContent($conditionFields);

        if($dbOrder){
            $dbOrder = $dbOrder[0];
            $arrFields = array();
            $arrFields['num'] = $dbOrder['num'] + 1;
            $arrFields['total'] = $dbOrder['total'] + $this->input->post('price');
            $arrFields['status'] = 1;
            $this->order_model->updateById($dbOrder['id'],$arrFields);
            $oid = $dbOrder['id'];
        }else{
            $arrFields = array();
            $arrFields['num'] = 1;
            $arrFields['total'] = $this->input->post('price');
            $arrFields['status'] = 1;
            $arrFields['cdate'] = date('Y-m-d H:i:s');
            $oid = $this->order_model->add($arrFields);
        }

        $arrFields = array();
        $arrFields['uid'] = $user['id'];
        $arrFields['mobile'] = $user['mobile'];
        $arrFields['price'] = $this->input->post('price');
        $arrFields['cdate'] = date('Y-m-d H:i:s');
        $arrFields['status'] = 1;
        $arrFields['oid'] = $oid;
        $this->order_list_model->add($arrFields);

        $this->show_message('添加成功！',site_url('/user/list_orders'));
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
