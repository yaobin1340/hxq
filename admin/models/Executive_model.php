<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Executive_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    
    public function list_notice($page)
    {
    	$data['limit'] = $this->limit;
    	//获取总记录数
    	$this->db->select('count(1) num')->from('notice_main');
    	if($this->input->post('title')){
    		$this->db->like('title',$this->input->post('title'));
    	}
    	$num = $this->db->get()->row();
    	$data['total'] = $num->num;
    
    	//搜索条件
    	$data['title'] = null;
    
    	//获取详细列
    	$this->db->select('a.id,title,a.cdate,b.rel_name')->from('notice_main a');
    	$this->db->join('users b','a.from_uid=b.id','left');
    	if($this->input->post('title')){
    		$this->db->like('title',$this->input->post('title'));
    		$data['title'] = $this->input->post('title');
    	}
    	$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
    	$data['items'] = $this->db->get()->result_array();
    
    	return $data;
    }
    
    public function save_notice(){
    	$user_info = $this->session->userdata('user_info');
    	$company = $this->session->userdata('company');
    	$data = array(
    			'title'=>$this->input->post('title'),
    			'content'=>$this->input->post('content'),
    			'from_uid'=>$user_info['id'],
    			'company_id'=>$company['id'],
    			'to_user'=>'',
    			'cdate'=>date('Y-m-d H:i:s',time())
    	);
    	 

    	$this->db->trans_start();//--------开始事务
    
    	$this->db->insert('notice_main',$data);
    	 

    	$this->db->trans_complete();//------结束事务
    	if ($this->db->trans_status() === FALSE) {
    		return -1;
    	} else {
    		return 1;
    	}
    }
    
    public function get_notice($id){
    	$data = $this->db->select('a.*,b.rel_name')->from('notice_main a')
    	->join('users b','a.from_uid=b.id','left')
    	->where('a.id',$id)->get()->row_array();
    	return $data;
    }


}