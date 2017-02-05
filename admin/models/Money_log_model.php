<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Money_log_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }

	public function list_money_log($page)
	{
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('money_log a');
		$this->db->join('users b','a.uid = b.id','left');
		if($this->input->post('keyword')){
			$this->db->like('b.rel_name',$this->input->post('keyword'));
			$this->db->or_like('b.mobile',$this->input->post('keyword'));
		}
		if($this->input->post('type')){
			$this->db->where('a.type',$this->input->post('type'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.cdate >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.cdate <=",$this->input->post('e_date')." 23:59:59");
		}
		$num = $this->db->get()->row();
		$data['total'] = $num->num;

		//搜索条件
		$data['keyword'] = $this->input->post('keyword')?$this->input->post('keyword'):null;
		$data['type'] = $this->input->post('type')?$this->input->post('type'):null;
		$data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;
		$data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
		//获取详细列
		$this->db->select('a.*,b.rel_name as u_name,b.mobile,c.name type_name')->from('money_log a');
		$this->db->join('users b','a.uid = b.id','left');
		$this->db->join('money_log_type c','a.type = c.id','left');
		if($this->input->post('keyword')){
			$this->db->like('b.rel_name',$this->input->post('keyword'));
			$this->db->or_like('b.mobile',$this->input->post('keyword'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.cdate >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.cdate <=",$this->input->post('e_date')." 23:59:59");
		}
		if($this->input->post('type')){
			$this->db->where('a.type',$this->input->post('type'));
		}
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
		$this->db->order_by('a.cdate','desc');
		$data['items'] = $this->db->get()->result_array();
		//获取总资金
		$this->db->select('sum(a.money) alltotal')->from('money_log a');
		$this->db->join('users b','a.uid = b.id','left');
		if($this->input->post('keyword')){
			$this->db->like('b.rel_name',$this->input->post('keyword'));
			$this->db->or_like('b.mobile',$this->input->post('keyword'));
		}
		if($this->input->post('type')){
			$this->db->where('a.type',$this->input->post('type'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.cdate >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.cdate <=",$this->input->post('e_date')." 23:59:59");
		}
		$num = $this->db->get()->row();
		$data['alltotal'] = $num->alltotal;
		return $data;
	}

	public function get_type_list(){
		$this->db->select();
		$this->db->from('money_log_type');
		return $this->db->get()->result_array();
	}

}