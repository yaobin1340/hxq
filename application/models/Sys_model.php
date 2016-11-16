<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Sys_model extends MY_Model
{

    public function __construct ()
    {
    	parent::__construct();
    }

    public function get_uid_byopenid($openid){
        $result = $this->db->select('*')->from('users')->where("openid",$openid)->get()->row_array();
        if($result){
            $this->session->set_userdata('uid', $result['id']);
        }
        return 1;
    }

}