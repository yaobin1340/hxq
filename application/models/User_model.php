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

    public function list_orders($page)
    {
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('order_list');
        if($this->input->post('keywords')){
            $this->db->where('uid',$this->input->post('keywords'));
            $this->db->or_where('mobile',$this->input->post('keywords'));
        }

        if($this->input->post('s_date')){
            $this->db->where("cdate >=",$this->input->post('s_date'));
        }

        if($this->input->post('e_date')){
            $this->db->where("cdate <=",$this->input->post('e_date')." 23:59:59");
        }
//        $this->db->where('shop_id',1); //TODO
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件
        $data['keywords'] = $this->input->post('keywords')?$this->input->post('keywords'):null;
        $data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
        $data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;

        //获取详细列
        $this->db->select('a.*,rel_name')->from('order_list a');
        $this->db->join('users b','a.uid=b.id','left');
        if($this->input->post('keywords')){
            $this->db->where('a.uid',$this->input->post('keywords'));
            $this->db->or_where('a.mobile',$this->input->post('keywords'));
        }
        if($this->input->post('s_date')){
            $this->db->where("a.cdate >=",$this->input->post('s_date'));
        }

        if($this->input->post('e_date')){
            $this->db->where("a.cdate <=",$this->input->post('e_date')." 23:59:59");
        }
//        $this->db->where('shop_id',1); //TODO
        $this->db->order_by('cdate','acs');
        $this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
        $data['items'] = $this->db->get()->result_array();

        return $data;
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


    public function del_order($id){
        $rs = $this->db->select('price,oid')->from('order_list')->where('id',$id)->where('status',1)->get()->row();
        if(!$rs){
            return -1;
        }
        $this->db->trans_start();//--------开始事务
        $this->db->where('id',$rs->oid);
        $this->db->set('num', 'num-1', FALSE);
        $this->db->set('total', 'total-'.$rs->price, FALSE);
        $this->db->update('order');

        $this->db->where('id',$id);
        $this->db->delete('order_list');
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

}