<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class User_model extends MY_Model
{
    protected $_tbName = 'users';
    protected $primaryKey = 'id';

    public function __construct ()
    {
    	parent::__construct();
    }
    
    public function get_user_info(){
        $this->db->select('a.*,b.name province_name,c.name city_name,d.name area_name')->from('users a');
        $this->db->join('province b','a.province_code = b.code','left');
        $this->db->join('city c','a.city_code = c.code','left');
        $this->db->join('area d','a.area_code = d.code','left');
        $this->db->where('a.id',$this->session->userdata('uid'));
        return $this->db->get()->row_array();
    }

    public function get_user($user_flag){
        return $result = $this->db->select('*')->from('users')->where("id = $user_flag or mobile = $user_flag")->get()->row_array();
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
            return false;
        }
    }

    function updateById($id,$arrFields){
        $condtionFields[$this->primaryKey] = $id;
        $this->db->where($condtionFields)->update($this->_tbName,$arrFields);
    }

    function add($arrFields){
        return $this->db->insert($this->_tbName,$arrFields);
    }

    public function save_information_revise($img){
        $data = array(
            'province_code'=>$this->input->post('province_code'),
            'city_code'=>$this->input->post('city_code'),
            'area_code'=>$this->input->post('area_code')
        );
        if($img){
            $data['face'] = $img;
        }
        $this->db->where('id',$this->session->userdata('uid'));
        $rs = $this->db->update('users',$data);
        if($rs){
            return 1;
        }else{
            return -1;
        }
    }

}