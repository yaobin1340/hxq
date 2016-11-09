<?php
if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class Order_model extends MY_Model
{

	public function __construct ()
	{
		parent::__construct();
	}

	public function list_orders($page)
	{
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('order');
		$this->db->where_in('status',array(2,3,-1));
		$num = $this->db->get()->row();
		$data['total'] = $num->num;

		//获取详细列
		$this->db->select('a.*,shop_name,percent')->from('order a');
		$this->db->join('shop b','a.shop_id=b.id','left');
		$this->db->where_in('a.status',array(2,3,-1));
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
		$data['items'] = $this->db->get()->result_array();

		return $data;
	}

	public function save_order(){
		$id = $this->input->post('id');
		$shop_id = $this->input->post('shop_id');
		$order_list = $this->db->select()->from('order_list')->where('oid',$id)->get()->result_array();
		$shop_info = $this->db->select()->from('shop')->where('id',$shop_id)->get()->row_array();
		$total_field = 'total'.$shop_info['percent'];
		$ax_field = 'ax'.$shop_info['percent'];






		$this->db->trans_start();//--------开始事务


		if($this->input->post('status') == 3){
			//会员累加金额,结算爱心
			foreach($order_list as $k=>$v){
				$user_info = $this->db->select()->from('users')->where('id',$v['uid'])->get()->row_array();
				$user_change = array(
					$total_field => $user_info[$total_field] + $v['price'],
					$ax_field => floor(($user_info[$total_field] + $v['price'])/50000)
				);
				$this->db->where('id',$v['uid']);
				$this->db->update('users',$user_change);
			}

			$shop_change = array(
				'total'=>$shop_info['total'] + $this->input->post('total'),
				'ax'=>floor(($shop_info['total'] + $this->input->post('total'))/50000)
			);

			$this->db->where('id',$shop_id);
			$this->db->update('shop',$shop_change);
		}


		$this->db->trans_complete();//------结束事务
		if ($this->db->trans_status() === FALSE) {
			return -1;
		} else {
			return 1;
		}
	}

	public function get_order_detail($id){
		$data['head'] = $this->db->select('a.*,shop_name,percent')->from('order a')
			->join('shop b','a.shop_id=b.id','left')
			->where('a.id',$id)
			->get()->row_array();

		$data['list'] = $this->db->select('a.*,rel_name')->from('order_list a')
			->join('users b','a.uid=b.id','left')
			->where('a.oid',$id)
			->get()->result_array();

		return $data;
	}




}