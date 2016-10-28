<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Common_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    
    public function get_notice_count(){
    	$user_info = $this->session->userdata('user_info');
    	$uid = $user_info['id'];
    	$rs = $this->db->select('count(1) num')->from('notice_list')->where('uid',$uid)->where('read',0)->get()->row();
    	return $rs->num;
    }
    
    public function list_notice($page)
    {
    	$user_info = $this->session->userdata('user_info');
    	$data['limit'] = $this->limit;
    	//获取总记录数
    	$this->db->select('count(1) num')->from('notice_list a');
    	$this->db->join('notice_main b','a.mid=b.id','left');
    	if($this->input->post('title')){
    		$this->db->like('title',$this->input->post('title'));
    	}
    	$this->db->where('uid',$user_info['id']);
    	$num = $this->db->get()->row();
    	$data['total'] = $num->num;
    
    	//搜索条件
    	$data['title'] = null;
    
    	//获取详细列
    	$this->db->select('b.id,b.title,b.cdate,c.rel_name,a.read')->from('notice_list a');
    	$this->db->join('notice_main b','a.mid=b.id','left');
    	$this->db->join('users c','b.from_uid=c.id','left');
    	if($this->input->post('title')){
    		$this->db->like('b.title',$this->input->post('title'));
    		$data['title'] = $this->input->post('title');
    	}
    	$this->db->where('a.uid',$user_info['id']);
    	$this->db->order_by('cdate','desc');
    	$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
    	$data['items'] = $this->db->get()->result_array();
    
    	return $data;
    }
    
    public function get_notice($id){
    	$user_info = $this->session->userdata('user_info');
    	
    	$data = $this->db->select('a.*,b.rel_name')->from('notice_main a')
    	->join('users b','a.from_uid=b.id','left')
    	->where('a.id',$id)->get()->row_array();
    	
    	$rs = $this->db->select('id,read')->from('notice_list')->where('mid',$id)->where('uid',$user_info['id'])->get()->row();
    	if(!$rs){
    		return -1;//不能查看别人的通知
    	}else{
    		if($rs->read == 0){
    			$this->db->where('id',$rs->id);
    			$this->db->update('notice_list',array('read'=>1));
    		}
    	}
    	
    	return $data;
    }
    
    public function get_user_list($dept_id) {
    	$user_info = $this->session->userdata('user_info');
    	$user_id = $user_info['id'];
    	return $this->db->select('id, rel_name')->get_where('users', array('dept_id' => $dept_id, 'id <>' => $user_id))->result_array();
    }
}