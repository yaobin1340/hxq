<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Main_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }

    public function display_bulletin() 
    {
    	return $this->db->from('bulletin')->where('checked', 1)->order_by('cdate', 'DESC')->limit(5)->get()->result_array();
    }
    
    public function display_bulletin_check() 
    {
    	$user_info = $this->session->userdata('user_info');
    	$this->db->select('bulletin.id, bulletin.title')->distinct()->from('bulletin_check');
    	$this->db->join('bulletin', 'bulletin_check.bid = bulletin.id', 'inner');
    	$this->db->where('bulletin_check.uid', $user_info['id']);
    	$this->db->where('bulletin_check.status', 1);
    	$this->db->where('bulletin.checked', 0);
    	return $this->db->get()->result_array();
    }
}