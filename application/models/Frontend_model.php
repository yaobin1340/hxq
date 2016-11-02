<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Frontend_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    
    public function get_city($province_code){
        return $this->db->select()->from('city')->where('provincecode',$province_code)->get()->result_array();
    }

    public function save_register($img){
        if(!$this->session->userdata('mobile')){
            return -1;
        }
        $data = array(
            'mobile'=>$this->session->userdata('mobile'),
            'password'=>sha1($this->input->post('password')),
            's_password'=>sha1($this->input->post('s_password')),
            'province_code'=>$this->input->post('province_code'),
            'city_code'=>$this->input->post('city_code'),
            'rel_name'=>$this->input->post('rel_name'),
            'id_no'=>$this->input->post('id_no'),
            'cdate'=>date('Y-m-d H:i:s',time()),
            'face'=>$img
        );

        if($this->db->insert('users',$data)){
            return 1;
        }else{
            return -1;
        }
    }

    public function get_province(){
        return $this->db->select()->from('province')->get()->result_array();
    }

    public function get_area($city_code){
        return $this->db->select()->from('area')->where('citycode',$city_code)->get()->result_array();
    }

    public function check_mobile($mobile){
        $rs = $this->db->select('id')->from('users')->where('mobile',$mobile)->get()->row();
        return $rs;
    }

    public function save_register_shop($img,$license){
        if(!$this->session->userdata('uid')){
            return -1;
        }
        $data = array(
            'uid'=>$this->session->userdata('uid'),
            'province_code'=>$this->input->post('province_code'),
            'city_code'=>$this->input->post('city_code'),
            'shop_name'=>$this->input->post('shop_name'),
            'parent_uid'=>$this->input->post('parent_uid'),
            'type'=>$this->input->post('type'),
            'address'=>$this->input->post('address'),
            'phone'=>$this->input->post('phone'),
            'person'=>$this->input->post('person'),
            'lat'=>$this->input->post('lat'),
            'lng'=>$this->input->post('lng'),
            'desc'=>$this->input->post('desc'),
            'business_time'=>$this->input->post('business_time'),
            'license'=>$license,
            'cdate'=>date('Y-m-d H:i:s',time()),
            'logo'=>$img
        );

        if($this->db->insert('shop',$data)){
            return 1;
        }else{
            return -1;
        }
    }

    public function get_shop_type(){
        return $this->db->select()->from('shop_type')->where('status',1)->get()->result_array();
    }
    
 
}