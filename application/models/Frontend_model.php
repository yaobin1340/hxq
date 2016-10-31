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

    public function save_register(){
        $data = array(
            'mobile'=>$this->session->userdata('mobile'),
            'password'=>sha1($this->input->post('password')),
            's_password'=>sha1($this->input->post('s_password')),
            'province_code'=>$this->input->post('province_code'),
            'city_code'=>$this->input->post('city_code'),
            'rel_name'=>$this->input->post('rel_name'),
            'id_no'=>$this->input->post('id_no'),
            'cdate'=>date('Y-m-d H:i:s',time())
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
    
 
}