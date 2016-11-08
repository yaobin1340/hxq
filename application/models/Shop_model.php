<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Shop_model extends MY_Model
{
    protected $_tbName = 'shop';
    protected $primaryKey = 'id';

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

    function queryByKey($id, $selectFields = '*') {
        $condtionFields [$this->primaryKey] = $id;

        return $this->queryContent($condtionFields, $selectFields);
    }

    function queryContent($condtionFields = array(), $selectFields = '*', $inField = '', $inArr = array()) {
        $this->db->select($selectFields)->from($this->_tbName);
        if($condtionFields || $inField){
            if($condtionFields) $this->db->where($condtionFields);
            if($inField) $this->db->where_in($inField,$inArr);
            return $this->db->get()->result_array();
        }else{
            return $this->db->select()->from($this->_tbName)->get()->result_array();
        }
    }

    function updateById($id,$arrFields){
        $condtionFields[$this->primaryKey] = $id;
        $this->db->where($condtionFields)->update($this->_tbName,$arrFields);
    }

    function add($arrFields){
        return $this->db->insert($this->_tbName,$arrFields);
    }
    
 
}