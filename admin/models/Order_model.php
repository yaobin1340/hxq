<?php
if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class Order_model extends MY_Model
{

	public function __construct ()
	{
		parent::__construct();
	}

	public function list_orders($page,$status)
	{
		$data['limit'] = $this->limit;
		$search_date = 'cdate';
		if($status == 1){
			$search_date = 'cdate';
		}else{
			$search_date = 'sdate';
		}
		//获取总记录数
		$this->db->select('count(1) num')->from('order a');
		$this->db->join('shop b','a.shop_id=b.id','left');
		if($status == 1){
			$this->db->where_in('a.status',array(1));
		}elseif($status==-1){
			$this->db->where_in('a.status',array(-1));
		}else{
			$this->db->where_in('a.status',array(2,3));
		}
		if($this->input->post('keyword')){
			$this->db->like('b.shop_name',$this->input->post('keyword'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.{$search_date} >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.{$search_date} <=",$this->input->post('e_date')." 23:59:59");
		}
		$num = $this->db->get()->row();
		$data['total'] = $num->num;
		$data['status'] = $status;
		$data['keyword'] = $this->input->post('keyword')?$this->input->post('keyword'):null;
		$data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;
		$data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
		//获取详细列
		$this->db->select('a.*,shop_name,percent')->from('order a');
		$this->db->join('shop b','a.shop_id=b.id','left');
		if($status == 1){
			$this->db->where_in('a.status',array(1));
		}elseif($status==-1){
			$this->db->where_in('a.status',array(-1));
		}else{
			$this->db->where_in('a.status',array(2,3));
		}
		if($this->input->post('keyword')){
			$this->db->like('b.shop_name',$this->input->post('keyword'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.{$search_date} >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.{$search_date} <=",$this->input->post('e_date')." 23:59:59");
		}
		$this->db->order_by('a.status','asc');
		$this->db->order_by($search_date,'desc');
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);

		$data['items'] = $this->db->get()->result_array();

		$ysday = date("Y-m-d",strtotime("-1 day"));
		$today = date("Y-m-d");
		$today= $this->db->select('sum(total) sum')->from('order')->where("DATE_FORMAT(sdate,'%Y-%m-%d')",$today)->get()->row_array();
		$ysday= $this->db->select('sum(total) sum')->from('order')->where("DATE_FORMAT(sdate,'%Y-%m-%d')",$ysday)->get()->row_array();
		$data['today_sum'] = $today['sum'];
		$data['ysday_sum'] = $ysday['sum'];
		//获取总金额
		$this->db->select("sum(a.total) alltotal,sum(a.total*(REPLACE(REPLACE(REPLACE(b.percent, '24', 24),'12',12),'6',6))/100) allneed,sum(a.num) allnum",false)->from('order a');
		$this->db->join('shop b','a.shop_id=b.id','left');
		if($status == 1){
			$this->db->where_in('a.status',array(1));
		}elseif($status==-1){
			$this->db->where_in('a.status',array(-1));
		}else{
			$this->db->where_in('a.status',array(2,3));
		}
		if($this->input->post('keyword')){
			$this->db->like('b.shop_name',$this->input->post('keyword'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.{$search_date} >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.{$search_date} <=",$this->input->post('e_date')." 23:59:59");
		}
		$num = $this->db->get()->row();
		$data['alltotal'] = $num->alltotal;
		$data['allneed'] = $num->allneed;
		$data['allnum'] = $num->allnum;
		return $data;
	}

	public function save_order(){
		$id = $this->input->post('id');
		$shop_id = $this->input->post('shop_id');
		$order_list = $this->db->select()->from('order_list')->where('oid',$id)->get()->result_array();
		$shop_info = $this->db->select()->from('shop')->where('id',$shop_id)->get()->row_array();
		$total_field = 'total'.$shop_info['percent'];
		$now_date = date('Y-m-d H:i:s');//新增加记录当前时间变量,下面所有操作的生成时间都取这个数
		//2016-12-2 新增加判断 审核通过时 上一日的settlement表数据是否生成,如果生产才可进行
		if($this->input->post('status') == 3){
			$yeday =date("Y-m-d",strtotime("-1 day"));
			$rs = $this->db->select()->from('settlement')->where('date',$yeday)->get()->row_array();
			if(!$rs){
				return -2;
			}
		}
		$this->db->trans_start();//--------开始事务

		if($this->input->post('status') == 3){
			$p = 1;
			if($shop_info['percent'] == 12){
				$p = 0.5;
			}
			if($shop_info['percent'] == 6){
				$p = 0.25;
			}

			foreach($order_list as $k=>$v){
				//会员累加金额,结算爱心
				$user_info = $this->db->select()->from('users')->where('id',$v['uid'])->get()->row_array();
				$count_ax = $this->db->select('count(1) num')->from('sunflower')
					->where('uid',$v['uid'])->where('percent',$shop_info['percent'])->get()->row();
				$user_change = array(
					$total_field => $user_info[$total_field] + $v['price'],
				);
				$this->db->where('id',$v['uid']);
				$this->db->update('users',$user_change);

				//会员新增爱心
				$insert_array = array();
				for($i=0;$i<floor(($user_info[$total_field] + $v['price'])/50000)-$count_ax->num;$i++){
					$insert_array[] = array(
						'cdate'=>$now_date,
						'percent'=>$shop_info['percent'],
						'uid'=>$v['uid']
					);
				}
				if($insert_array){
					$this->db->insert_batch('sunflower', $insert_array);
				}


				//会员的推荐人获得0.6的返利
				if($user_info['parent_id']){
					$this->money_log($v['price']*0.006*$p,3,'推荐客户奖励',$user_info['parent_id']);
				}
			}

			//商家累加金额和获得爱心
			$count_ax_shop = $this->db->select('count(1) num')->from('sunflower_shop')
				->where('shop_id',$shop_id)->where('percent',$shop_info['percent'])->get()->row();

			$shop_change = array(
				'total'=>$shop_info['total'] + $this->input->post('total'),
			);
			$this->db->where('id',$shop_id);
			$this->db->update('shop',$shop_change);


			$insert_array = array();
			for($i=0;$i<floor(($shop_info['total'] + $this->input->post('total'))/50000)-$count_ax_shop->num;$i++){
				$insert_array[] = array(
					'cdate'=>$now_date,
					'percent'=>$shop_info['percent'],
					'shop_id'=>$shop_id
				);
			}
			if($insert_array){
				$this->db->insert_batch('sunflower_shop', $insert_array);
			}



			//商家上级获得分红
			if($shop_info['parent_uid'] > 0){
				$parent_info = $this->db->select()->from('users')->where('id',$shop_info['parent_uid'])->get()->row_array();
				//商家上级是普通业务员
				if($parent_info && $parent_info['is_dl'] == 1){
					$this->money_log($this->input->post('total')*0.006*$p,4,'业务员推荐商家奖励',$parent_info['id']);

					$p_parent_info = $this->db->select()->from('users')->where('id',$parent_info['parent_id'])->get()->row_array();
					//上上级是服务商
					if($p_parent_info && $p_parent_info['is_dl'] == 2){
						$this->money_log($this->input->post('total')*0.006*$p,7,'服务商下级业务员推荐奖励',$p_parent_info['id']);
					}
					//上上级是联合服务商
					if($p_parent_info && $p_parent_info['is_dl'] == 3){
						$this->money_log($this->input->post('total')*0.008*$p,9,'联合服务商下级业务员推荐奖励',$p_parent_info['id']);
					}
				}

				//商家上级是服务商
				if($parent_info && $parent_info['is_dl'] == 2){
					$this->money_log($this->input->post('total')*0.012*$p,5,'服务商推荐商家奖励',$parent_info['id']);

					$p_parent_info = $this->db->select()->from('users')->where('id',$parent_info['parent_id'])->get()->row_array();

					//上上级是联合服务商
					if($p_parent_info && $p_parent_info['is_dl'] == 3){
						$this->money_log($this->input->post('total')*0.002*$p,8,'联合服务商下级服务商推荐奖励',$p_parent_info['id']);
					}
				}

				//商家上级是联合服务商
				if($parent_info && $parent_info['is_dl'] == 3){
					$this->money_log($this->input->post('total')*0.014*$p,6,'联合服务商推荐商家奖励',$parent_info['id']);
				}
			}
		}

		$this->db->where('id',$id);
		$this->db->update('order',array('status'=>$this->input->post('status'),'adate'=>$now_date));

		$this->db->where('oid',$id);
		$this->db->update('order_list',array('status'=>$this->input->post('status')));

		$this->db->trans_complete();//------结束事务
		if ($this->db->trans_status() === FALSE) {
			return -1;
		} else {
			return 1;
		}
	}

	public function money_log($money,$type,$remark,$uid){
		$this->db->where('id',$uid);
		$this->db->set('integral', 'integral+'.$money, FALSE);
		$this->db->update('users');

		$this->db->insert('money_log',array(
			'remark'=>$remark,
			'money'=>$money,
			'type'=>$type,
			'uid'=>$uid,
			'cdate' => date('Y-m-d H:i:s')
		));
	}

	public function get_order_detail($id){
		$data['head'] = $this->db->select('a.*,shop_name,percent')->from('order a')
			->join('shop b','a.shop_id=b.id','left')
			->where('a.id',$id)
			->get()->row_array();

		$data['list'] = $this->db->select('a.*,rel_name')->from('order_list a')
			->join('users b','a.uid=b.id','left')
			->where('a.oid',$id)
			->get()->result_array();

		return $data;
	}




}