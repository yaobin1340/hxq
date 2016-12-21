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

    public function list_orders($page)
    {
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('order_list a');
        $this->db->join('users e','a.uid=e.id','left');
        if($this->input->post('keywords')){
            $this->db->like('e.rel_name',$this->input->post('keywords'));
            $this->db->or_like('a.mobile',$this->input->post('keywords'));
        }
        $this->db->join('order b','b.id = a.oid','left');
        $this->db->join('shop c','c.id = b.shop_id','left');
        $this->db->join('users d','d.id = c.uid','left');
        $this->db->where('d.id =',$this->session->userdata('uid'));
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
        $data['keywords'] = $this->input->post('keywords')?$this->input->post('keywords'):null;
        $data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
        $data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;

        //获取详细列
        $this->db->select('a.*,e.rel_name')->from('order_list a');
        $this->db->join('users e','a.uid=e.id','left');
        $this->db->join('order b','b.id = a.oid','left');
        $this->db->join('shop c','c.id = b.shop_id','left');
        $this->db->join('users d','d.id = c.uid','left');
        $this->db->where('d.id =',$this->session->userdata('uid'));
        if($this->input->post('keywords')){
            $this->db->like('e.rel_name',$this->input->post('keywords'));
            $this->db->or_like('a.mobile',$this->input->post('keywords'));
        }
        if($this->input->post('s_date')){
            $this->db->where("a.cdate >=",$this->input->post('s_date'));
        }

        if($this->input->post('e_date')){
            $this->db->where("a.cdate <=",$this->input->post('e_date')." 23:59:59");
        }
//        $this->db->where('shop_id',1); //TODO
        $this->db->order_by('a.cdate','desc');
        $this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }

    public function list_order_audit($page){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('order b');
        $this->db->join('shop c','c.id = b.shop_id','left');
        $this->db->join('users d','d.id = c.uid','left');
        $this->db->where('d.id =',$this->session->userdata('uid'));
        if($this->input->post('s_date')){
            $this->db->where("b.cdate >=",$this->input->post('s_date'));
        }

        if($this->input->post('e_date')){
            $this->db->where("b.cdate <=",$this->input->post('e_date')." 23:59:59");
        }
//        $this->db->where('shop_id',1); //TODO
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件
        $data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
        $data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;

        //获取详细列
        $this->db->select('b.*')->from('order b');
        $this->db->join('shop c','c.id = b.shop_id','left');
        $this->db->join('users d','d.id = c.uid','left');
        $this->db->where('d.id =',$this->session->userdata('uid'));
        if($this->input->post('s_date')){
            $this->db->where("b.cdate >=",$this->input->post('s_date'));
        }

        if($this->input->post('e_date')){
            $this->db->where("b.cdate <=",$this->input->post('e_date')." 23:59:59");
        }
//        $this->db->where('shop_id',1); //TODO
        $this->db->order_by('b.cdate','desc');
        $this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }

    public function get_name_by_keywords($keywords){
        if(!$keywords) return false;
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
        $rs1 = $this->db->select()->from('order_list a')
            ->join('order b','a.oid = b.id','inner')
            ->join('shop c','b.shop_id = c.id','inner')
            ->where('a.id',$id)
            ->where('c.uid',$this->session->userdata('uid'))->get()->row();
        if(!$rs1){
            return -2;
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

    public function save_order(){
        $user_flag = $this->input->post('user_flag');
        $price = $this->input->post('price')*100;

        $user_info = $this->db->select('id,mobile')->from('users')
            ->where('id',$user_flag)
            ->or_where('mobile',$user_flag)->get()->row();

        if(!$user_info)
            return -2;//用户不存在
        if($user_info->id==$this->session->userdata('uid'))
            return -3;//用户不存在
        $rs = $this->db->select('id')->from('order')
            ->where('shop_id',$this->session->userdata('shop_id'))
            ->where('status',1)
            ->get()->row();

        $this->db->trans_start();//--------开始事务
        if($rs){
            $this->db->where('id',$rs->id);
            $this->db->set('num', 'num+1', FALSE);
            $this->db->set('total', 'total+'.$price, FALSE);
            $this->db->update('order');
            $oid = $rs->id;
        }else{
            $this->db->insert('order',array(
                'num'=>1,
                'total'=>$price,
                'status'=>1,
                'cdate'=>date('Y-m-d H:i:s',time()),
                'shop_id'=>$this->session->userdata('shop_id')
            ));
            $oid = $this->db->insert_id();
        }

        $this->db->insert('order_list',array(
            'uid'=>$user_info->id,
            'mobile'=>$user_info->mobile,
            'price'=>$price,
            'shop_id'=>$this->session->userdata('shop_id'),
            'status'=>1,
            'oid'=>$oid,
            'cdate'=>date('Y-m-d H:i:s',time())
        ));

        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function order_detail($order_id){
        $row = $this->db->select()->from('order')->where('id',$order_id)->get()->row_array();
        if(!$row){
            return -1;
        }
        $check = $this->db->select()->from('shop')->where(array(
            'id'=>$row['shop_id'],
            'uid'=>$this->session->userdata('uid'),
        ))->get()->row_array();
        if(!$check){
            return -2;
        }
        $row['percent']=$check['percent'];
        //$data['data'] = $row;
        return $row;
    }

    public function tijiao_order($order_id,$order_pic){
        if(!($uid = $this->session->userdata('uid'))){
            return -3;
        }
        $row = $this->db->select()->from('order')->where('id',$order_id)->get()->row_array();
        if(!$row){
            return -1;
        }
        $check = $this->db->select()->from('shop')->where(array(
            'id'=>$row['shop_id'],
            'uid'=>$this->session->userdata('uid'),
        ))->get()->row();
        if(!$check){
            return -2;
        }
        if($row['status']!=1){
            return -4;
        }
        $data = array(
            'pic'=>$order_pic,
            'remark'=>$this->input->post('remark'),
            'sdate'=>date('Y-m-d H:i:s',time()),
            'status'=>2,//待審核
        );
        $this->db->trans_start();//--------开始事务
        $this->db->where('id',$order_id)->update('order',$data);
        $this->db->where('oid',$order_id)->update('order_list',array(
            'status'=>2
        ));
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -5;
        } else {
            return 1;
        }
    }
    public function order_byoid($order_id){
        $this->db->select('a.*,e.rel_name')->from('order_list a');
        $this->db->join('users e','a.uid=e.id','left');
        $this->db->join('order b','b.id = a.oid','left');
        return $this->db->where('b.id',$order_id)->get()->result_array();
    }

    public function sum_count_shop($id){
        $this->db->select('count(1) num')->from('sunflower_shop');
        $this->db->where('shop_id',$id);
        $this->db->where('status',1);
        $row = $this->db->get()->row_array();
        return $row['num'];
    }

    public function get_province($code = null){
        $this->db->select()->from('province');
        if($code) $this->db->where('code',$code);
        return $this->db->get()->result_array();
    }

    public function get_shop_type(){
        return $this->db->select()->from('shop_type')->where('status',1)->get()->result_array();
    }

    public function getSessionUser($uid){
        $user = $this->tableQueryContent('users',array('id'=>$uid),array('id','mobile'));
        return $user[0];
    }

    public function save_shop_info($imgs){
        if(!($uid = $this->session->userdata('uid'))){
            return -1;
        }
        $data = array(
            'uid'=>$this->session->userdata('uid'),
            'province_code'=>$this->input->post('province_code'),
            'city_code'=>$this->input->post('city_code'),
            'area_code'=>$this->input->post('area_code'),
            'shop_name'=>$this->input->post('shop_name'),
            'type'=>$this->input->post('type'),
            'address'=>$this->input->post('address'),
            'phone'=>$this->input->post('phone'),
            'person'=>$this->input->post('person'),
            'lat'=>$this->input->post('lat'),
            'lng'=>$this->input->post('lng'),
            'baidu_lat'=>$this->input->post('baidu_lat')?$this->input->post('baidu_lat'):$this->input->post('lat'),
            'baidu_lng'=>$this->input->post('baidu_lng')?$this->input->post('baidu_lng'):$this->input->post('lng'),
            'desc'=>$this->input->post('desc'),
            'business_time'=>$this->input->post('business_time'),
            'license'=>$imgs['license']?$imgs['license']:'',
            //'cdate'=>date('Y-m-d H:i:s',time()),
            'logo'=>$imgs['logo']?$imgs['logo']:'',
            'cns1'=>$imgs['cns1']?$imgs['cns1']:'',
//            'cns2'=>$imgs['cns2']?$imgs['cns2']:'',
            'sfz1'=>$imgs['sfz1']?$imgs['sfz1']:'',
//            'sfz2'=>$imgs['sfz2']?$imgs['sfz2']:'',
        );
        if(!$imgs['logo'])unset($data['logo']);
        if(!$imgs['license'])unset($data['license']);
        if(!$imgs['cns1'])unset($data['cns1']);
//        if(!$imgs['cns2'])unset($data['cns2']);
        if(!$imgs['sfz1'])unset($data['sfz1']);
//        if(!$imgs['sfz2'])unset($data['sfz2']);

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
}