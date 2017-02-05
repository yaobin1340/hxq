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
		$search_date = 'cdate';
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('withdraw a');
		$this->db->join('users b','a.uid = b.id','left');
		if($this->input->post('keyword')){
			$this->db->like('b.rel_name',$this->input->post('keyword'));
			$this->db->or_like('b.mobile',$this->input->post('keyword'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.{$search_date} >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.{$search_date} <=",$this->input->post('e_date')." 23:59:59");
		}
		$this->db->where('a.status',$status);
		$num = $this->db->get()->row();
		$data['total'] = $num->num;

		//搜索条件
		$data['status'] = $status?$status:null;
		$data['keyword'] = $this->input->post('keyword')?$this->input->post('keyword'):null;
		$data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;
		$data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
		//获取详细列
		$this->db->select('a.*,b.rel_name as u_name,b.mobile')->from('withdraw a');
		$this->db->join('users b','a.uid = b.id','left');
		if($this->input->post('keyword')){
			$this->db->like('b.rel_name',$this->input->post('keyword'));
			$this->db->or_like('b.mobile',$this->input->post('keyword'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.{$search_date} >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.{$search_date} <=",$this->input->post('e_date')." 23:59:59");
		}
		$this->db->where('a.status',$status);
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
		if($status == 1){
			$this->db->order_by('a.id','desc');
		}else{
			$this->db->order_by('a.adate','desc');
		}
		$data['items'] = $this->db->get()->result_array();
		//获取总金额
		$this->db->select('sum(a.money) alltotal')->from('withdraw a');
		$this->db->join('users b','a.uid = b.id','left');
		if($this->input->post('keyword')){
			$this->db->like('b.rel_name',$this->input->post('keyword'));
			$this->db->or_like('b.mobile',$this->input->post('keyword'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.{$search_date} >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.{$search_date} <=",$this->input->post('e_date')." 23:59:59");
		}
		$this->db->where('a.status',$status);
		$num = $this->db->get()->row();
		$data['alltotal'] = $num->alltotal;
		return $data;
	}

	public function withdraw_detail($id){
		$this->db->select('a.*,b.rel_name as u_name,b.mobile,b.id_no,b.face')->from('withdraw a');
		$this->db->join('users b','a.uid=b.id','left');
		$this->db->where('a.id',$id);
		return $this->db->get()->row_array();
	}

	public function down_excel(){
		$search_date = 'cdate';
		$this->db->select('a.*,b.rel_name as u_name,b.mobile')->from('withdraw a');
		$this->db->join('users b','a.uid = b.id','left');
		if($this->input->post('keyword')){
			$this->db->like('b.rel_name',$this->input->post('keyword'));
			$this->db->or_like('b.mobile',$this->input->post('keyword'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.{$search_date} >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.{$search_date} <=",$this->input->post('e_date')." 23:59:59");
		}
		$this->db->where('a.status',$this->input->post('type'));
		$this->db->where("a.flag",1);
		$this->db->order_by('a.id','desc');
		$rs = $this->db->get()->result_array();
		return $rs;
	}

	public function audit_withdraw_get($id){
		$rs = $this->db->where('id',$id)->update('withdraw',array('status'=>2,'adate'=>date('Y-m-d H:i:s',time())));
		if($rs){
			return 1;
		}else{
			return -1;
		}
	}



}