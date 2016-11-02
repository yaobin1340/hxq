<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Basic_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    
    public function list_department($page)
    {
    	$data['limit'] = $this->limit;
    	//获取总记录数
    	$this->db->select('count(1) num')->from('department');
    	if($this->input->post('department_name')){
    		$this->db->like('name',$this->input->post('department_name'));
    	}
    	$num = $this->db->get()->row();
    	$data['total'] = $num->num;
    	 
    	//搜索条件
    	$data['department_name'] = null;
    	 
    	//获取详细列
    	$this->db->select()->from('department');
    	if($this->input->post('department_name')){
    		$this->db->like('name',$this->input->post('department_name'));
    		$data['department_name'] = $this->input->post('department_name');
    	}
    	$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
    	$data['items'] = $this->db->get()->result_array();
    	 
    	return $data;
    }
    
    public function save_department(){
    	$data = array(
    		'name'=>$this->input->post('department_name'),
    		'pid'=>$this->input->post('pid'),
    		'status'=>$this->input->post('status'),
    		'cdate'=>date('Y-m-d H:i:s',time())
    	);
    	 
    	$this->db->trans_start();//--------开始事务
    	 
    	if($this->input->post('id')){//修改
    		$this->db->where('id',$this->input->post('id'));
    		$this->db->update('department',$data);
    	}else{//新增
    		$this->db->insert('department',$data);
    	}
    	 
    	$this->db->trans_complete();//------结束事务
    	if ($this->db->trans_status() === FALSE) {
    		return -1;
    	} else {
    		return 1;
    	}
    }
    
    public function del_department($id){
    	$this->db->trans_start();//--------开始事务
    
    	$this->db->where('id',$id);
    	$this->db->delete('department');
    	 
    	$this->db->trans_complete();//------结束事务
    	if ($this->db->trans_status() === FALSE) {
    		return -1;
    	} else {
    		return 1;
    	}
    }
    
    public function get_department($id){
    	return $this->db->select()->from('department')->where('id',$id)->get()->row_array();
    }
    
    public function get_company($id){
    	return $this->db->select()->from('company')->where('id',$id)->get()->row_array();
    }
    
    public function get_department_list() {
    	$where = ' select department.id, CONCAT(company.name, "-", department.name) AS name from department ';
    	$where .= ' left join company on department.pid = company.id ';
    	return $this->db->query($where)->result_array();
    }
    
    public function get_house_list() {
    	return $this->db->select()->from('house')->get()->result_array();
    }
}