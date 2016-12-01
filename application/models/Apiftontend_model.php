<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/11/30
 * Time: 上午11:05
 */
class Apiftontend_model extends MY_Model{
    public function __construct ()
    {
        parent::__construct();
    }

    public function check_login(){
        $rs = $this->db->select('id')->from('users')
            ->where('status',1)
            ->where('mobile',$this->input->post('mobile'))
            ->where('password',sha1($this->input->post('password')))
            ->get()->row();
        if($rs){
            return $rs->id;
        }else{
            return -1;
        }
    }

    public function check_mobile($mobile){
        $rs = $this->db->select('id')->from('users')->where('mobile',$mobile)->get()->row();
        return $rs;
    }

    public function save_register($img){
        if(!$this->input->post('mobile')){
            return -1;
        }
        $rs = $this->db->select('count(1) num')->from('users')->where('mobile',$this->input->post('mobile'))->get()->row();
        if($rs->num > 0){
            return -1;
        }
        $data = array(
            'parent_id'=>$this->input->post('parent_id'),
            'mobile'=>$this->input->post('mobile'),
            'password'=>sha1($this->input->post('password')),
            's_password'=>sha1($this->input->post('s_password')),
            'province_code'=>$this->input->post('province_code'),
            'city_code'=>$this->input->post('city_code'),
            'area_code'=>$this->input->post('area_code'),
            'rel_name'=>$this->input->post('rel_name'),
            'id_no'=>$this->input->post('id_no'),
            'cdate'=>date('Y-m-d H:i:s',time()),
            'face'=>$img
        );

        if($this->db->insert('users',$data)){
            $uid = $this->db->insert_id();
            return $uid;
        }else{
            return -1;
        }
    }

    public function get_naid_by_keywords($keywords){
        if(!$keywords) return false;
        $rs = $this->db->select('rel_name,id')->from('users')
            ->where('id',$keywords)
            ->or_where('mobile',$keywords)
            ->get()->row_array();
        if($rs)
            return $rs;
        else
            return null;
    }

    public function get_city($province_code = null){
        $this->db->select()->from('city');
        if($province_code) $this->db->where('provincecode',$province_code);
        return $this->db->get()->result_array();
    }

    public function get_province($code = null){
        $this->db->select()->from('province');
        if($code) $this->db->where('code',$code);
        return $this->db->get()->result_array();
    }

    public function get_area($city_code = null){
        $this->db->select()->from('area');
        if($city_code) $this->db->where('citycode',$city_code);
        return $this->db->get()->result_array();
    }
}