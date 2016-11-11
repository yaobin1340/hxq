<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Settlement_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    
    public function list_settlements($page)
    {
    	$data['limit'] = $this->limit;
    	//获取总记录数
    	$this->db->select('count(1) num')->from('settlement');

    	$num = $this->db->get()->row();
    	$data['total'] = $num->num;

    	//获取详细列
    	$this->db->select()->from('settlement');

    	$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
    	$data['items'] = $this->db->get()->result_array();
    
    	return $data;
    }
    



}