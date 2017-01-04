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
            return $row;
        }else{
            return -1;
        }
    }

    public function change_order($order_id){
        $res = $this->db->where('id',$order_id)->update('user_order',array('status'=>2));
        return $res;
    }
 
}