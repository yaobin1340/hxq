<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Withdraw_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    


	public function save_audit_withdraw(){
		$data = array(
			'status'=>$this->input->post('status'),
			'remark'=>$this->input->post('remark'),
			'adate'=>date('Y-m-d H:i:s',time()),
		);

		$this->db->trans_start();//--------开始事务
		$row = $this->db->select()->from('withdraw')->where('id',$this->input->post('id'))->get()->row_array();
		if($row['status'] == 1){
			if($data['status']==-1){
				$this->db->insert('money_log',array(
					'type'=>10,
					'uid'=>$row['uid'],
					'cdate'=>date('Y-m-d H:i:s'),
					'remark'=>'提现失败,退回激励',
					'money'=>$row['money']
				));
				$this->db->where('id',$row['uid']);
				$this->db->set('integral',"integral + {$row['money']}",false);
				$this->db->update('users');
			}
			$this->db->where('id',$this->input->post('id'));
			$this->db->update('withdraw',$data);
		}

		$this->db->trans_complete();//------结束事务
		if ($this->db->trans_status() === FALSE) {
			return -1;
		} else {
			return 1;
		}
	}

	public function list_withdraw($page,$status)
	{
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('withdraw a');
		$this->db->join('users b','a.uid = b.id','left');
		if($this->input->post('keyword')){
			$this->db->like('b.rel_name',$this->input->post('keyword'));
			$this->db->or_like('b.mobile',$this->input->post('keyword'));
		}
		$this->db->where('a.status',$status);
		$num = $this->db->get()->row();
		$data['total'] = $num->num;

		//搜索条件
		$data['status'] = $status?$status:null;
		$data['keyword'] = $this->input->post('keyword')?$this->input->post('keyword'):null;
		//获取详细列
		$this->db->select('a.*,b.rel_name as u_name,b.mobile')->from('withdraw a');
		$this->db->join('users b','a.uid = b.id','left');
		if($this->input->post('keyword')){
			$this->db->like('b.rel_name',$this->input->post('keyword'));
			$this->db->or_like('b.mobile',$this->input->post('keyword'));
		}
		$this->db->where('a.status',$status);
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
		if($status == 1){
			$this->db->order_by('a.id','desc');
		}else{
			$this->db->order_by('a.adate','desc');
		}
		$data['items'] = $this->db->get()->result_array();

		return $data;
	}

	public function withdraw_detail($id){
		$this->db->select('a.*,b.rel_name as u_name,b.mobile,b.id_no,b.face')->from('withdraw a');
		$this->db->join('users b','a.uid=b.id','left');
		$this->db->where('a.id',$id);
		return $this->db->get()->row_array();
	}




}