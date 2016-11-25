<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Index_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    
    public function index(){
    	return $this->db->select()->from('admin')->get()->row_array();
    }

    public function get_index_count(){
        $audit_shop = $this->db->select('count(1) num')->from('shop')->where('status',1)->get()->row();
        $shop = $this->db->select('count(1) num')->from('shop')->where_in('status',array(2,-2))->get()->row();

        $data = array(
            'audit_shop_count'=>$audit_shop->num,
            'shop_count'=>$shop->num,
        );
        return $data;
    }

    public function get_sett_info(){
        $sql = "select * from (select * from settlement order by date desc limit 14) aa order by aa.date asc";
        $rs = $this->db->query($sql)->result_array();

        return $rs;
    }

    public function index_data(){
        $data = array();
        $data['users_num']=$this->db->select('count(1) num')->from('users')->get()->row()->num;
        $data['shop_num']=$this->db->select('count(1) num')->from('shop')->where('status',2)->get()->row()->num;
        $data['ljxf']=$this->db->select('sum(total) alltotal')->from('shop')->get()->row()->alltotal;
        $data['sunflower_user_num']=$this->db->select('count(1) num')->from('sunflower')->where('status',1)->get()->row()->num;
        $data['sunflower_shop_num']=$this->db->select('count(1) num')->from('sunflower_shop')->where('status',1)->get()->row()->num;
        $data['order_pay_total_3']=$this->db->select('sum((a.total*b.percent/100)) alltotal')
            ->from('order a')
            ->join('shop b','a.shop_id = b.id','left')
            ->where('a.status',3)->get()->row()->alltotal;
        $data['order_pay_total_2']=$this->db->select('sum((a.total*b.percent/100)) alltotal')
            ->from('order a')
            ->join('shop b','a.shop_id = b.id','left')
            ->where('a.status',2)->get()->row()->alltotal;
        $data['withdraw_total_2']=$this->db->select('sum(money) alltotal')->from('withdraw')->where('status',2)->get()->row()->alltotal;
        $data['withdraw_total_1']=$this->db->select('sum(money) alltotal')->from('withdraw')->where('status',1)->get()->row()->alltotal;
        return $data;
    }
 
}