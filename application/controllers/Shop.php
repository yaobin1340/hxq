<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends MY_Controller {

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
		$this->load->model('shop_model');
		$shop_info = $this->shop_model->get_shop_info();
		if(!$shop_info){
			$this->show_message('您还不是商家,请先申请入驻',site_url('/frontend/register_shop'));
		}
		$this->session->set_userdata('shop_id',$shop_info['id']);
		$this->assign('user_info', $shop_info);
	}

	public function index()
	{
		$this->display('shop/shop_center.html');
	}

	public function list_orders($page = 1){
		$this->assign('header_name', '订单管理');
//		$data = $this->shop_model->list_orders($page);
//		$this->assign('data', $data);
//		$this->display('shop/list_orders.html');
		//		$data = $this->shop_model->list_orders($page);
		$this->assign('s_date', $this->input->post('s_date'));
		$this->assign('e_date', $this->input->post('e_date'));
		$this->assign('keywords', $this->input->post('keywords'));
		$this->display('shop/list_orders.html');
	}

	public function list_orders_loaddata($page = 1){
		$data = $this->shop_model->list_orders($page);
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->display('shop/list_orders_loaddata.html');
	}

	public function get_name_by_keywords($keywords){
		echo $this->shop_model->get_name_by_keywords($keywords);
	}

	public function del_order($id){
		echo $this->shop_model->del_order($id);
	}

	public function list_order_audit(){
		$this->assign('header_name', '订单提交');
		$this->assign('s_date', $this->input->post('s_date'));
		$this->assign('e_date', $this->input->post('e_date'));
		$this->display('shop/list_order_audit.html');
	}

	public function list_order_audit_loaddata($page = 1){
		$data = $this->shop_model->list_order_audit($page);
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->display('shop/list_order_audit_loaddata.html');
	}

	public function save_order(){
		$rs = $this->shop_model->save_order();
		if($rs == 1){
			$this->show_message('添加成功！',site_url('shop/list_orders'));
		}else if($rs == -2){
			$this->show_message('用户不存在！');
		}else{
			$this->show_message('操作失败！');
		}

	}

	public function order_detail($order_id){
		$this->assign('header_name', '订单详情');
		$data = $this->shop_model->order_detail($order_id);
		if($data == -1){
			$this->show_message('订单不存在！');
		}else if($data == -2){
			$this->show_message('不能看别人的订单！');
		}else{
			$data['items'] = $this->shop_model->order_byoid($order_id);
			$this->assign('data', $data);
			$this->display('shop/order_detail.html');
		}

	}

	public function tijiao_order($order_id){
		$order_Pic = $this->upload('order_pic','pic');
		$rs = $this->shop_model->tijiao_order($order_id,$order_Pic);
		if($rs == 1){
			$this->show_message('提交成功！',site_url('shop/list_order_audit'));
		}else if($rs == -1){
			$this->show_message('订单不存在！');
		}else if($rs == -2){
			$this->show_message('不能操作别人的订单！');
		}else if($rs == -3){
			$this->show_message('未登陆！');
		}else if($rs == -4){
			$this->show_message('提交失败！');
		}else{
			$this->show_message('操作失败！');
		}
	}

    public function show_information()
    {
        $this->display('shop/show_information.html');
    }

}
