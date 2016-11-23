<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Commonweal_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }

	public function list_commonweal($page)
	{
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('commonweal a');
		if($this->input->post('status')){
			$this->db->where('a.status',$this->input->post('status'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.date >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.date <=",$this->input->post('e_date')." 23:59:59");
		}
		$num = $this->db->get()->row();
		$data['total'] = $num->num;

		//搜索条件
		$data['status'] = $this->input->post('status')?$this->input->post('status'):null;
		$data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;
		$data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
		//获取详细列
		$this->db->select('a.*')->from('commonweal a');
		if($this->input->post('status')){
			$this->db->where('a.status',$this->input->post('status'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.date >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.date <=",$this->input->post('e_date')." 23:59:59");
		}
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
		$this->db->order_by('a.id','desc');
		$data['items'] = $this->db->get()->result_array();

		return $data;
	}

	public function change_status($id){
		$rs = $this->db->where('id',$id)->update('commonweal',array('status'=>2));
		if($rs){
			return 1;
		}else{
			return -1;
		}
	}

}