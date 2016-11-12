<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Settlement_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    
    public function list_settlements($page)
    {
    	$data['limit'] = $this->limit;
    	//获取总记录数
    	$this->db->select('count(1) num')->from('settlement');

    	$num = $this->db->get()->row();
    	$data['total'] = $num->num;

    	//获取详细列
    	$this->db->select()->from('settlement');

    	$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
    	$data['items'] = $this->db->get()->result_array();
    
    	return $data;
    }

	public function settlement(){
		$date = date("Y-m-d",strtotime("-1 day"));
		$rs = $this->db->select()->from('settlement')->where('date',$date)->get()->row();
		if($rs)//已经结算过了,不能重复结算
			return -2;

		$this->db->select('sum(num) num,sum(a.total) total,percent')->from('order a');
		$this->db->join('shop b','a.shop_id=b.id','left');
		$this->db->where("DATE_FORMAT(a.adate,'%Y-%m-%d')",$date);
		$this->db->where('a.status',3);
		$this->db->group_by('percent');
		$data = $this->db->get()->result_array();
		$total6 = $total12 = $total24 = 0;
		$num6 = $num12 = $num24 = 0;
		$shop_ax6 = $shop_ax12 = $shop_ax24 = 0;
		$ax6_price = $ax12_price = $ax24_price =0;
		$shop_ax6_price = $shop_ax12_price = $shop_ax24_price = 0;
		$ax6 = $ax12 = $ax24 = 0;
		foreach ($data as $k=>$v){
			if($v['percent'] == 6){
				$total6 = $v['total'];
				$num6 = $v['num'];
			}
			if($v['percent'] == 12){
				$total12 = $v['total'];
				$num12 = $v['num'];
			}
			if($v['percent'] == 24){
				$total24 = $v['total'];
				$num24 = $v['num'];
			}
		}

		$this->db->trans_start();//--------开始事务


		$this->db->insert('settlement',array(
			'total6'=>$total6,
			'total12'=>$total12,
			'total24'=>$total24,
			'num6'=>$num6,
			'num12'=>$num12,
			'num24'=>$num24,
			'date'=>$date
		));

		$id = $this->db->insert_id();

		$data = $this->db->select('percent,sum(status) ax')->from('sunflower')->where('status',1)->group_by('percent')->get()->result_array();

		foreach($data as $k=>$v){
			if($v['percent'] == 6){
				$ax6 = $v['ax'];
			}
			if($v['percent'] == 12){
				$ax12 = $v['ax'];
			}
			if($v['percent'] == 24){
				$ax24 = $v['ax'];
			}
		}

		$this->db->where('id',$id);
		$this->db->update('settlement',array(
			'ax6'=>$ax6,
			'ax12'=>$ax12,
			'ax24'=>$ax24,
		));

		$data = $this->db->select('percent,sum(status) ax')->from('sunflower_shop')->where('status',1)->group_by('percent')->get()->result_array();
		foreach($data as $k=>$v){
			if($v['percent'] == 6){
				$shop_ax6 = $v['ax'];
			}
			if($v['percent'] == 12){
				$shop_ax12 = $v['ax'];
			}
			if($v['percent'] == 24){
				$shop_ax24 = $v['ax'];
			}
		}

		$this->db->where('id',$id);
		$this->db->update('settlement',array(
			'shop_ax6'=>$shop_ax6,
			'shop_ax12'=>$shop_ax12,
			'shop_ax24'=>$shop_ax24,
		));

		$rs = $this->db->select()->from('settlement')->where('id',$id)->get()->row_array();
		if($rs['ax6'] > 0 && $rs['total6'] > 0){
			$ax6_price = floor($rs['total6']*0.041/$rs['ax6']);
		}
		if($rs['ax12'] > 0 && $rs['total12'] > 0){
			$ax12_price = floor($rs['total12']*0.082/$rs['ax12']);
		}
		if($rs['ax24'] > 0 && $rs['total24'] > 0){
			$ax24_price = floor($rs['total24']*0.164/$rs['ax24']);
		}

		if($rs['shop_ax6'] > 0 && $rs['total6'] > 0){
			$shop_ax6_price = floor($rs['total6']*0.009/$rs['shop_ax6']);
		}
		if($rs['shop_ax12'] > 0 && $rs['total12'] > 0){
			$shop_ax12_price = floor($rs['total12']*0.018/$rs['shop_ax12']);
		}
		if($rs['shop_ax24'] > 0 && $rs['total24'] > 0){
			$shop_ax24_price = floor($rs['total24']*0.036/$rs['shop_ax24']);
		}

		$this->db->where('id',$id);
		$this->db->update('settlement',array(
			'ax6_price'=>$ax6_price,
			'ax12_price'=>$ax12_price,
			'ax24_price'=>$ax24_price,
			'shop_ax6_price'=>$shop_ax6_price,
			'shop_ax12_price'=>$shop_ax12_price,
			'shop_ax24_price'=>$shop_ax24_price,
		));

		$this->db->trans_complete();//------结束事务
		if ($this->db->trans_status() === FALSE) {
			return -1;
		} else {
			return 1;
		}
	}

	public function settlement_detail($id){
		return $this->db->select()->from('settlement')->where('id',$id)->get()->row_array();
	}

	public function audit_settlement($id){
		$data = $this->db->select()->from('settlement')->where('id',$id)->get()->row();
		if($data->status != 1){
			return -1;
		}
		$this->db->trans_start();//--------开始事务
		//用户返利6系列
		$this->db->where('status',1);
		$this->db->where('percent',6);
		$this->db->where('return_integral <',12500);
		$this->db->set('return_integral', 'return_integral+'.$data->ax6_price, FALSE);
		$this->db->update('sunflower');

		//用户返利12系列
		$this->db->where('status',1);
		$this->db->where('percent',12);
		$this->db->where('return_integral <',25000);
		$this->db->set('return_integral', 'return_integral+'.$data->ax12_price, FALSE);
		$this->db->update('sunflower');

		//用户返利24系列
		$this->db->where('status',1);
		$this->db->where('percent',24);
		$this->db->where('return_integral <',50000);
		$this->db->set('return_integral', 'return_integral+'.$data->ax24_price, FALSE);
		$this->db->update('sunflower');

		//返满后溢出金额
		$rs6 = $this->db->select('sum(return_integral-12500) overflow_integral,uid')->from('sunflower')
			->where('status',1)->where('return_integral >',12500)->where('percent',6)->group_by('uid')->get()->result_array();

		$rs12 = $this->db->select('sum(return_integral-25000) overflow_integral,uid')->from('sunflower')
			->where('status',1)->where('return_integral >',25000)->where('percent',6)->group_by('uid')->get()->result_array();

		$rs24 = $this->db->select('sum(return_integral-50000) overflow_integral,uid')->from('sunflower')
			->where('status',1)->where('return_integral >',50000)->where('percent',6)->group_by('uid')->get()->result_array();

		$overflow_integral6 = array();
		$overflow_integral12 = array();
		$overflow_integral24 = array();
		$add_integral = array();
		foreach($rs6 as $k=>$v){
			$overflow_integral6[$v['uid']] = $v['overflow_integral'];
		}
		foreach($rs12 as $k=>$v){
			$overflow_integral12[$v['uid']] = $v['overflow_integral'];
		}
		foreach($rs24 as $k=>$v){
			$overflow_integral24[$v['uid']] = $v['overflow_integral'];
		}
		$rs = $this->db->select('sum(status) ax,percent,uid')->from('sunflower')->where('status',1)->group_by(array("percent", "uid"))->get()->result_array();
		foreach($rs as $k=>$v){
			if(!isset($add_integral[$v['uid']])){
				$add_integral[$v['uid']] = 0;
			}
			if($v['percent'] == 6){//6系
				$add_integral[$v['uid']] += $rs['ax']*$data->ax6_price;
			}
			if($v['percent'] == 12){//12系
				$add_integral[$v['uid']] += $rs['ax']*$data->ax12_price;
			}
			if($v['percent'] == 24){//24系
				$add_integral[$v['uid']] += $rs['ax']*$data->ax24_price;
			}
		}

		foreach($add_integral as $uid=>$v){
			$integral = $v;
			if(isset($overflow_integral6[$uid])){
				$integral = $integral - $overflow_integral6[$uid];
			}
			if(isset($overflow_integral12[$uid])){
				$integral = $integral - $overflow_integral12[$uid];
			}
			if(isset($overflow_integral24[$uid])){
				$integral = $integral - $overflow_integral24[$uid];
			}
			$this->db->where('id',$uid);
			$this->db->set('integral', 'integral+'.$integral, FALSE);
			$this->db->update('users');

			$this->db->insert('money_log',array(
				'remark'=>'向日葵返利(会员)',
				'money'=>$integral,
				'type'=>2,
				'uid'=>$uid,
				'cdate' => date('Y-m-d H:i:s')
			));
		}
		//TODO 商家返利
		//商家返利6系列
		$this->db->where('status',1);
		$this->db->where('percent',6);
		$this->db->where('return_integral <',2700);
		$this->db->set('return_integral', 'return_integral+'.$data->shop_ax6_price, FALSE);
		$this->db->update('sunflower_shop');

		//商家返利12系列
		$this->db->where('status',1);
		$this->db->where('percent',12);
		$this->db->where('return_integral <',5400);
		$this->db->set('return_integral', 'return_integral+'.$data->shop_ax12_price, FALSE);
		$this->db->update('sunflower_shop');

		//商家返利24系列
		$this->db->where('status',1);
		$this->db->where('percent',24);
		$this->db->where('return_integral <',10800);
		$this->db->set('return_integral', 'return_integral+'.$data->shop_ax24_price, FALSE);
		$this->db->update('sunflower_shop');

		//返满后溢出金额
		$rs = $this->db->select('sum(return_integral-(450*a.`percent`)) overflow_integral,shop_id,a.percent,b.uid')->from('sunflower_shop a')
			->join('shop b','a.shop_id=b.id')
			->where('a.status',1)->where('return_integral >','(450*a.`percent`)')
			->group_by(array('shop_id','a.percent'))->get()->result_array();

		$overflow_integral = array();
		$add_integral = array();
		foreach($rs as $k=>$v){
			$overflow_integral[$v['uid']] = $v['overflow_integral'];
		}
		$rs = $this->db->select('sum(a.status) ax,a.percent,uid')->from('sunflower_shop a')
			->join('shop b','a.shop_id=b.id')
			->where('a.status',1)
			->group_by(array("a.percent", "uid"))->get()->result_array();

		foreach($rs as $k=>$v){
			if(!isset($add_integral[$v['uid']])){
				$add_integral[$v['uid']] = 0;
			}
			if($v['percent'] == 6){//6系
				$add_integral[$v['uid']] += $rs['ax']*$data->shop_ax6_price;
			}
			if($v['percent'] == 12){//12系
				$add_integral[$v['uid']] += $rs['ax']*$data->shop_ax12_price;
			}
			if($v['percent'] == 24){//24系
				$add_integral[$v['uid']] += $rs['ax']*$data->shop_ax24_price;
			}
		}

		foreach($add_integral as $uid=>$v){
			if(isset($overflow_integral[$uid])){
				$integral = $v - $overflow_integral[$uid];
				$this->db->where('id',$uid);
				$this->db->set('integral', 'integral+'.$integral, FALSE);
				$this->db->update('users');

				$this->db->insert('money_log',array(
					'remark'=>'向日葵返利(商家)',
					'money'=>$integral,
					'type'=>2,
					'uid'=>$uid,
					'cdate' => date('Y-m-d H:i:s')
				));
			}
		}

		$this->db->where('percent',24);
		$this->db->where('return_integral >=',50000);
		$this->db->update('sunflower',array(
			'status'=>2,
			'return_integral'=>50000
		));

		$this->db->where('percent',12);
		$this->db->where('return_integral >=',25000);
		$this->db->update('sunflower',array(
			'status'=>2,
			'return_integral'=>25000
		));

		$this->db->where('percent',6);
		$this->db->where('return_integral >=',12500);
		$this->db->update('sunflower',array(
			'status'=>2,
			'return_integral'=>12500
		));

		$this->db->where('percent',24);
		$this->db->where('return_integral >=',10800);
		$this->db->update('sunflower_shop',array(
			'status'=>2,
			'return_integral'=>10800
		));

		$this->db->where('percent',12);
		$this->db->where('return_integral >=',5400);
		$this->db->update('sunflower_shop',array(
			'status'=>2,
			'return_integral'=>5400
		));

		$this->db->where('percent',6);
		$this->db->where('return_integral >=',2700);
		$this->db->update('sunflower_shop',array(
			'status'=>2,
			'return_integral'=>2700
		));

		$this->db->trans_complete();//------结束事务
		if ($this->db->trans_status() === FALSE) {
			return -1;
		} else {
			return 1;
		}


	}



}