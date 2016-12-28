<?php
if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class Goods_model extends MY_Model
{

	public function __construct ()
	{
		parent::__construct();
	}
	public function list_goods($page=1){
		$search_date = 'cdate';
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('goods a');
		$this->db->join('goods_type b','a.type_id = b.id','left');
		if($this->input->post('keyword')){
			$this->db->like('a.good_name',$this->input->post('keyword'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.{$search_date} >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.{$search_date} <=",$this->input->post('e_date')." 23:59:59");
		}
		$num = $this->db->get()->row();
		$data['total'] = $num->num;

		//搜索条件
		$data['keyword'] = $this->input->post('keyword')?$this->input->post('keyword'):null;
		$data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;
		$data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
		//获取详细列
		$this->db->select('a.*')->from('goods a');
		$this->db->join('goods_type b','a.type_id = b.id','left');
		if($this->input->post('keyword')){
			$this->db->like('a.good_name',$this->input->post('keyword'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.{$search_date} >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.{$search_date} <=",$this->input->post('e_date')." 23:59:59");
		}
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
		$this->db->order_by('a.id','desc');
		$data['items'] = $this->db->get()->result_array();

		return $data;
	}

}