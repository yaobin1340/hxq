<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Super_id_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }

	public function list_super_id($page)
	{
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('super_id a');
		$this->db->join('users b','a.super_uid = b.id','left');
		if($this->input->post('super_uid')){
			$this->db->like('a.super_uid',$this->input->post('super_uid'));
		}
		$num = $this->db->get()->row();
		$data['total'] = $num->num;

		//搜索条件
		$data['super_uid'] = $this->input->post('super_uid')?$this->input->post('super_uid'):null;
		//获取详细列
		$this->db->select('a.*,b.rel_name')->from('super_id a');
		$this->db->join('users b','a.super_uid = b.id','left');
		if($this->input->post('super_uid')){
			$this->db->like('a.super_uid',$this->input->post('super_uid'));
		}
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
		$this->db->order_by('a.id','asc');
		$data['items'] = $this->db->get()->result_array();

		return $data;
	}

	public function save_super_id(){
		$data = array(
			'super_uid'=>$this->input->post('super_uid'),
			'remark'=>$this->input->post('remark')
		);
		$super_id = $this->db->select()->from('super_id')->where('super_uid',$this->input->post('super_uid'))->get()->row_array();
		if($super_id){
			return -2;
		}
		if($this->input->post('super_id')){
			$rs = $this->db->where('id',$this->input->post('super_id'))->update('super_id',$data);
		}else{
			$rs = $this->db->insert('super_id',$data);
		}

		if($rs){
			return 1;
		}else{
			return -1;
		}
	}

	public function get_super_id($id){
		$rs = $this->db->select()->where('id',$id)->from('super_id')->get()->row_array();
		if($rs){
			return $rs;
		}
		return -1;

	}

	public function delete_super_id($id){
		$rs =  $this->db->where('id',$id)->delete('super_id');
		if($rs){
			return 1;
		}
		return -1;
	}
}