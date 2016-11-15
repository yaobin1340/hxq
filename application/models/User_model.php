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

    public function find($id){
        return $result = $this->db->select('*')->from('users')->where(array('id'=>$id))->get()->row_array();
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
        $this->db->where('uid',$this->session->userdata('uid'));

        if($this->input->post('s_date')){
            $this->db->where("cdate >=",$this->input->post('s_date'));
        }

        if($this->input->post('e_date')){
            $this->db->where("cdate <=",$this->input->post('e_date')." 23:59:59");
        }

        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件
        $data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
        $data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;

        //获取详细列
        $this->db->select('a.*,shop_name,address')->from('order_list a');
        $this->db->join('users b','a.uid=b.id','left');
        $this->db->join('shop c','a.shop_id=c.id','left');

        $this->db->where('a.uid',$this->session->userdata('uid'));

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

    public function list_order_audit($page){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('order');

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
        $data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
        $data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;

        //获取详细列
        $this->db->select()->from('order');

        if($this->input->post('s_date')){
            $this->db->where("cdate >=",$this->input->post('s_date'));
        }

        if($this->input->post('e_date')){
            $this->db->where("cdate <=",$this->input->post('e_date')." 23:59:59");
        }
//        $this->db->where('shop_id',1); //TODO
        $this->db->order_by('cdate','desc');
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

    public function withdraw(){
        $data['user_info'] = $this->db->select()->from('users')->where('id',$this->session->userdata('uid'))->get()->row_array();
        $data['withdraw_info'] = $this->db->select()
            ->from('withdraw')
            ->where('uid',$this->session->userdata('uid'))
            ->order_by('id','desc')
            ->get()->row_array();
        return $data;
    }

    public function save_withdraw(){
        if(!($uid = $this->session->userdata('uid'))){
            return -1;
        }
        $user_info = $this->db->select()->from('users')->where('id',$this->session->userdata('uid'))->get()->row_array();
        if((int)$this->input->post('money') > $user_info['integral']/100){
            return -2;
        }
        $data = array(
            'uid'=>$this->session->userdata('uid'),
            'money' => (int)$this->input->post('money')*100,
            'bank' => trim($this->input->post('bank')),
            'bank_no' => trim($this->input->post('bank_no')),
            'bank_branch' => trim($this->input->post('bank_branch')),
            'rel_name' => trim($this->input->post('rel_name')),
            'cdate' => date('Y-m-d H:i:s'),
            'status'=>1
        );
        $this->db->trans_start();//--------开始事务
        $this->db->insert('withdraw',$data);
        $this->db->where('id',$this->session->userdata('uid'));
        $this->db->set('integral',"integral - {$data['money']}",false);
        $this->db->update('users');
            $this->db->insert('money_log',array(
                'uid'=>$this->session->userdata('uid'),
                'cdate' => date('Y-m-d H:i:s'),
                'type'=>1,
                'remark'=>'用户提现',
                'money'=>'-'.(string)$data['money']
            ));
        $this->db->trans_complete();//------结束事务

        if ($this->db->trans_status() === FALSE) {
            return -5;
        } else {
            return 1;
        }
    }

    public function list_withdraw_loaddata($page = 1){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('withdraw a');
        $this->db->where('a.uid',$this->session->userdata('uid'));
//        $this->db->where('shop_id',1); //TODO
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件

        //获取详细列
        $this->db->select('a.*')->from('withdraw a');
        $this->db->where('a.uid',$this->session->userdata('uid'));
        $this->db->limit($data['limit'], $offset = ($page - 1) * $data['limit']);
        $this->db->order_by('a.id','desc');
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }

    public function money_log_list_loaddata($page = 1){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('money_log a');
        $this->db->where('a.uid',$this->session->userdata('uid'));
        if($this->input->post('s_date')){
            $this->db->where("a.cdate >=",$this->input->post('s_date'));
        }

        if($this->input->post('e_date')){
            $this->db->where("a.cdate <=",$this->input->post('e_date')." 23:59:59");
        }
//        $this->db->where('shop_id',1); //TODO
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件
        $data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
        $data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;
        //获取详细列
        $this->db->select('a.*')->from('money_log a');
        $this->db->where('a.uid',$this->session->userdata('uid'));
        if($this->input->post('s_date')){
            $this->db->where("a.cdate >=",$this->input->post('s_date'));
        }

        if($this->input->post('e_date')){
            $this->db->where("a.cdate <=",$this->input->post('e_date')." 23:59:59");
        }
        $this->db->limit($data['limit'], $offset = ($page - 1) * $data['limit']);
        $this->db->order_by('a.id','desc');
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }

    public function user_heart_loaddata($page=1){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('sunflower a');
        $this->db->where('a.uid',$this->session->userdata('uid'));
        switch($this->input->post('tab_type')){
            case 1:
                $this->db->where("a.percent",6);
                break;
            case 2:
                $this->db->where("a.percent",12);
                break;
            case 3:
                $this->db->where("a.percent",24);
                break;

        }
//        $this->db->where('shop_id',1); //TODO
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件
        $data['tab_type'] = $this->input->post('tab_type')?$this->input->post('tab_type'):null;
        //获取详细列
        $this->db->select('a.*')->from('sunflower a');
        $this->db->where('a.uid',$this->session->userdata('uid'));
        switch($this->input->post('tab_type')){
            case 1:
                $this->db->where("a.percent",6);
                break;
            case 2:
                $this->db->where("a.percent",12);
                break;
            case 3:
                $this->db->where("a.percent",24);
                break;

        }
        $this->db->limit($data['limit'], $offset = ($page - 1) * $data['limit']);
        $this->db->order_by('a.cdate','desc');
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }
}