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
 
}