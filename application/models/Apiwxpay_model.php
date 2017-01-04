<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Apiwxpay_model extends MY_Model
{


    public function __construct ()
    {
    	parent::__construct();
    }

    public function get_order($order_id){
        $this->db->select()->from('user_order');
        $this->db->where('id',$order_id);
        $this->db->where('status',1);
        $row = $this->db->get()->row_array();
        if($row){
            if($row['need_pay']<=0){
                $this->db->where('id',$order_id);
                $this->db->update('user_order',array('status',2));
                return -2;
            }else{
                return $row;
            }
        }else{
            return -1;
        }
    }

    public function save_pay_log($order_id){
        $this->db->select()->from('user_order');
        $this->db->where('id',$order_id);
        $this->db->where('status',1);
        $row = $this->db->get()->row_array();
        if($row){
            if($row['need_pay']<=0){
                $this->db->where('id',$order_id);
                $this->db->update('user_order',array('status',2));
                return -2;
            }else{
                $this->db->insert('pay_code',array(
                    'pay_price'=>$row['need_pay'],
                    'uo_id'=>$order_id,
                    'pay_code'=>$row['pay_code'],
                    'cdate'=>date('Y-m-d H:i:s')
                ));
                return $this->db->insert_id();
            }
        }else{
            return -1;
        }
    }

    public function change_order($order_id){
        $pay_log = $this->db->select()->from('pay_log')->where('id',$order_id)->get()->row_array();
        if(!$pay_log){
            return false;
        }
        $this->db->trans_start();
        $this->db->where('id',$order_id)->update('pay_log',array('flag'=>1));
        $this->db->where('id',$pay_log['uo_id'])->update('user_order',array('status'=>2));
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return false;
        } else {
            return true;
        }
    }
 
}