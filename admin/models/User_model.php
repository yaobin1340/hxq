<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class User_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    

    public function change_status($id,$status){
		$this->db->trans_start();//--------开始事务
		$this->db->where('id',$id);
		$this->db->update('users',array('status'=>$status));
		$this->db->trans_complete();//------结束事务
		if ($this->db->trans_status() === FALSE) {
			return -1;
		} else {
			return 1;
		}
    }


	public function list_users($page,$type)
	{
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('users');
		if($this->input->post('keyword')){
			$this->db->like('rel_name',$this->input->post('keyword'));
			$this->db->or_like('mobile',$this->input->post('keyword'));
		}
		$this->db->where('is_dl',$type);
		$num = $this->db->get()->row();
		$data['total'] = $num->num;

		//搜索条件
		$data['keyword'] = $this->input->post('keyword')?$this->input->post('keyword'):null;

		//获取详细列
		$this->db->select()->from('users');
		if($this->input->post('keyword')){
			$this->db->like('rel_name',$this->input->post('keyword'));
			$this->db->or_like('mobile',$this->input->post('keyword'));
		}
		$this->db->where('is_dl',$type);
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
		$data['items'] = $this->db->get()->result_array();

		return $data;
	}

	public function get_user_detail($id){
		$this->db->select('a.*,b.name province_name,c.name city_name,d.name area_name')->from('users a');
		$this->db->join('province b','a.province_code=b.code','left');
		$this->db->join('city c','a.city_code=c.code','left');
		$this->db->join('area d','a.area_code=d.code','left');
		$this->db->where('a.id',$id);
		return $this->db->get()->row_array();
	}




}