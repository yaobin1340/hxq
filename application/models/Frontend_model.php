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
        $rs = $this->db->select('count(1) num')->from('users')->where('mobile',$this->session->userdata('mobile'))->get()->row();
        if($rs->num > 0){
            return -1;
        }
        $data = array(
            'mobile'=>$this->session->userdata('mobile'),
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
            $this->session->set_userdata('uid',$uid);
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
        if(!($uid = $this->session->userdata('uid'))){
            return -1;
        }
        $data = array(
            'uid'=>$this->session->userdata('uid'),
            'province_code'=>$this->input->post('province_code'),
            'city_code'=>$this->input->post('city_code'),
            'area_code'=>$this->input->post('area_code'),
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
            'logo'=>$img,
            'status'=>1,//待审核
            'percent'=>$this->input->post('percent'),
        );
        if(!$img)unset($data['logo']);
        if(!$license)unset($data['license']);

        //邀请人
        $parent_flag = $this->input->post('parent_flag');
        if($parent_flag){
            $parent_user = $this->db->select('id,mobile')->from('users')
                ->where('id',$parent_flag)
                ->or_where('mobile',$parent_flag)
                ->get()->row();
            if($parent_user){
                $data['parent_uid'] = $parent_user->id;
                $data['parent_mobile'] = $parent_user->mobile;
            }
        }

        $shop = $this->db->select()->from('shop')->where("uid = $uid")->get()->row_array();
        if($shop){
            if($this->db->where(array('id'=>$shop['id']))->update('shop',$data)){
                return 1;
            }else{
                return -1;
            }
        }else{
            if($this->db->insert('shop',$data)){
                return 1;
            }else{
                return -1;
            }
        }
    }

    public function get_shop_type(){
        return $this->db->select()->from('shop_type')->where('status',1)->get()->result_array();
    }

    public function check_login(){
        $rs = $this->db->select('id')->from('users')
            ->where('status',1)
            ->where('mobile',$this->input->post('mobile'))
            ->where('password',sha1($this->input->post('password')))
            ->get()->row();
        if($rs){
            $this->session->set_userdata('uid',$rs->id);
            return 1;
        }else{
            return -1;
        }
    }

    public function change_pwd(){
        $this->db->where('mobile',$this->session->userdata('mobile'));
        $rs = $this->db->update('users',array('password' => sha1($this->input->post('password'))));
        if($rs)
            return 1;
        else
            return -1;
    }

    public function getSessionUser($uid){
        $user = $this->tableQueryContent('users',array('id'=>$uid),array('id','mobile'));
        return $user[0];
    }

    public function get_name_by_keywords($keywords){
        $rs = $this->db->select('rel_name')->from('users')
            ->where('id',$keywords)
            ->or_where('mobile',$keywords)
            ->get()->row();
        if($rs)
            return $rs->rel_name;
        else
            return null;
    }
 
}