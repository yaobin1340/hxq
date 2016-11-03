<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Shop_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    
    public function get_shop_info(){
        $this->db->select('a.*,b.name province_name,c.name city_name,d.name area_name')->from('shop a');
        $this->db->join('province b','a.province_code = b.code','left');
        $this->db->join('city c','a.city_code = c.code','left');
        $this->db->join('area d','a.area_code = d.code','left');
        $this->db->where('uid',$this->session->userdata('uid'));
        $this->db->where('a.status',2);
        return $this->db->get()->row_array();
    }
    
 
}