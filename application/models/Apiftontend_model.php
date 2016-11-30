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
}