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
    
 
}