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
    
 
}