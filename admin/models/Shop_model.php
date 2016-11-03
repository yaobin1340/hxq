<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Shop_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    
    public function list_shop_type($page)
    {
    	$data['limit'] = $this->limit;
    	//获取总记录数
    	$this->db->select('count(1) num')->from('shop_type');

    	$num = $this->db->get()->row();
    	$data['total'] = $num->num;

    	//获取详细列
    	$this->db->select()->from('shop_type');

    	$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
    	$data['items'] = $this->db->get()->result_array();
    
    	return $data;
    }
    
    public function save_shop_type(){
    	$data = array(
			'name'=>$this->input->post('name'),
			'status'=>1,
    	);
    	 
    	$this->db->trans_start();//--------开始事务
    	$this->db->insert('shop_type',$data);
    	$this->db->trans_complete();//------结束事务
    	if ($this->db->trans_status() === FALSE) {
    		return -1;
    	} else {
    		return 1;
    	}
    }

	//$status 1启用,2停用
    public function change_status($id,$status){
		$this->db->trans_start();//--------开始事务
		$this->db->where('id',$id);
		$this->db->update('shop_type',array('status'=>$status));
		$this->db->trans_complete();//------结束事务
		if ($this->db->trans_status() === FALSE) {
			return -1;
		} else {
			return 1;
		}
    }


	public function list_shop_audit($page)
	{
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('shop');
		if($this->input->post('shop_name')){
			$this->db->like('shop_name',$this->input->post('shop_name'));
		}
		$this->db->where('status',1);
		$num = $this->db->get()->row();
		$data['total'] = $num->num;

		//搜索条件
		$data['shop_name'] = $this->input->post('shop_name')?$this->input->post('shop_name'):null;

		//获取详细列
		$this->db->select('a.*,b.name province_name,c.name city_name,d.name type_name,e.name area_name')->from('shop a');
		$this->db->join('province b','a.province_code=b.code','left');
		$this->db->join('city c','a.city_code=c.code','left');
		$this->db->join('shop_type d','a.type=d.id','left');
		$this->db->join('area e','a.area_code=e.code','left');
		if($this->input->post('shop_name')){
			$this->db->like('shop_name',$this->input->post('shop_name'));
		}
		$this->db->where('a.status',1);
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
		$data['items'] = $this->db->get()->result_array();

		return $data;
	}

	public function get_audit_shop($id){
		$this->db->select('a.*,b.name province_name,c.name city_name,d.name type_name,e.rel_name rel_name,f.name area_name')->from('shop a');
		$this->db->join('province b','a.province_code=b.code','left');
		$this->db->join('city c','a.city_code=c.code','left');
		$this->db->join('shop_type d','a.type=d.id','left');
		$this->db->join('users e','a.parent_uid=e.id','left');
		$this->db->join('area f','a.area_code=f.code','left');
		$this->db->where('a.id',$id);
		return $this->db->get()->row_array();
	}

	public function save_audit_shop(){
		$data = array(
			'status'=>$this->input->post('status'),
			'remark'=>$this->input->post('remark'),
			'adate'=>date('Y-m-d H:i:s',time()),
		);

		$this->db->trans_start();//--------开始事务
		$this->db->where('id',$this->input->post('id'));
		$this->db->update('shop',$data);
		$this->db->trans_complete();//------结束事务
		if ($this->db->trans_status() === FALSE) {
			return -1;
		} else {
			return 1;
		}
	}

	public function list_shop($page){
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('shop');
		if($this->input->post('shop_name')){
			$this->db->like('shop_name',$this->input->post('shop_name'));
		}
		$this->db->where_in('status',array(2,-2));
		$num = $this->db->get()->row();
		$data['total'] = $num->num;

		//搜索条件
		$data['shop_name'] = $this->input->post('shop_name')?$this->input->post('shop_name'):null;

		//获取详细列
		$this->db->select('a.*,b.name province_name,c.name city_name,d.name type_name,e.name area_name')->from('shop a');
		$this->db->join('province b','a.province_code=b.code','left');
		$this->db->join('city c','a.city_code=c.code','left');
		$this->db->join('shop_type d','a.type=d.id','left');
		$this->db->join('area e','a.area_code=e.code','left');
		if($this->input->post('shop_name')){
			$this->db->like('shop_name',$this->input->post('shop_name'));
		}
		$this->db->where_in('a.status',array(2,-2));
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
		$data['items'] = $this->db->get()->result_array();

		return $data;
	}

	public function get_shop_detail($id){
		$this->db->select('a.*,b.name province_name,c.name city_name,d.name type_name,e.rel_name rel_name,f.name area_name')->from('shop a');
		$this->db->join('province b','a.province_code=b.code','left');
		$this->db->join('city c','a.city_code=c.code','left');
		$this->db->join('shop_type d','a.type=d.id','left');
		$this->db->join('users e','a.parent_uid=e.id','left');
		$this->db->join('area f','a.area_code=f.code','left');
		$this->db->where('a.id',$id);
		return $this->db->get()->row_array();
	}

	//$status 2启用,-2停用
	public function non_use_shop($id,$status){
		$this->db->trans_start();//--------开始事务
		$this->db->where('id',$id);
		$this->db->update('shop',array('status'=>$status));
		$this->db->trans_complete();//------结束事务
		if ($this->db->trans_status() === FALSE) {
			return -1;
		} else {
			return 1;
		}
	}


}