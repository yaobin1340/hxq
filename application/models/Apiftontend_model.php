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
            //这里新增查找是不是靓号,如果是就加3 保持编号
            $super_id = $this->db->select()->from('super_id')->where('super_uid',$uid)->get()->row_array();
            if($super_id){
                $new_uid = $uid + 3;
                $this->db->where("id",$uid)->set('id',$new_uid)->update('users');
                return $new_uid;
            }else{
                return $uid;
            }
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

    public function get_area_name($code){
        return $this->db->select('name')->from('area')->where('code',$code)->get()->row_array();
    }

    public function index_loaddata($page=1,$app_uid){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('shop');

        if($this->input->post('type')){
            $this->db->where("type",$this->input->post('type'));
        }
        if($this->input->post('province_code')){
            $this->db->where("province_code",$this->input->post('province_code'));
        }
        if($this->input->post('city_code')){
            $this->db->where("city_code",$this->input->post('city_code'));
        }
        if($this->input->post('area_code')){
            $this->db->where("area_code",$this->input->post('area_code'));
        }
        if($this->input->post('shop_name')){
            $this->db->like("shop_name",$this->input->post('shop_name'));
        }
        $this->db->where('status',2);
//        $this->db->where('shop_id',1); //TODO
        $num = $this->db->get()->row();
        $data['total'] = $num->num;
        $lng = $this->input->post('lng')?$this->input->post('lng'):0;
        $lat = $this->input->post('lat')?$this->input->post('lat'):0;
        //搜索条件
        /*$data['province_code'] = $this->input->post('province_code')?$this->input->post('province_code'):null;
        $data['city_code'] = $this->input->post('city_code')?$this->input->post('city_code'):null;
        $data['area_code'] = $this->input->post('area_code')?$this->input->post('area_code'):null;
        $data['type'] = $this->input->post('type')?$this->input->post('type'):null;
        $data['lat'] = $this->input->post('lat')?$this->input->post('lat'):0;
        $data['lng'] = $this->input->post('lng')?$this->input->post('lng'):0;
        $data['shop_name'] = $this->input->post('shop_name')?$this->input->post('shop_name'):null;*/
        //获取详细列
        $this->db->select("*,
        ROUND(lat_lng_distance({$lat}, {$lng}, lat, lng), 2) AS juli,
        ",false)->from('shop');
        $this->db->where('lat <>','');
        $this->db->where('lng <>','');
        //$this->db->where('lng is not null');
        if($this->input->post('type')){
            $this->db->where("type",$this->input->post('type'));
        }
        if($this->input->post('province_code')){
            $this->db->where("province_code",$this->input->post('province_code'));
        }
        if($this->input->post('city_code')){
            $this->db->where("city_code",$this->input->post('city_code'));
        }
        if($this->input->post('area_code')){
            $this->db->where("area_code",$this->input->post('area_code'));
        }
        if($this->input->post('shop_name')){
            $this->db->like("shop_name",$this->input->post('shop_name'));
        }
//        $this->db->where('shop_id',1); //TODO
        $this->db->where('status',2);
        $this->db->order_by('juli','asc');
        $this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
        $data['items'] = $this->db->get()->result_array();
        if($this->input->post('area_code') && $app_uid > 0){
            $this->db->where('id',$app_uid)
                ->update('users',array('u_area_code'=>$this->input->post('area_code')));
            // echo $this->db->last_query();
        }
        return $data;
    }

    public function get_shop_type(){
        return $this->db->select()->from('shop_type')->where('status',1)->get()->result_array();
    }

    public function nearcity($area_name){
        return $this->db->select('name,code')->from('area')->where('name',$area_name)->get()->row_array();
    }

    public function change_pwd(){
        $this->db->where('mobile',$this->input->post('mobile'));
        $rs = $this->db->update('users',array('password' => sha1($this->input->post('password'))));
        if($rs)
            return 1;
        else
            return -1;
    }

    public function show_information()
    {
        $this->db->select();
        $this->db->from('settlement');
        $this->db->where('date <= now()');
        $this->db->order_by('date','desc');
        $row = $this->db->get()->row_array();
        //var_dump($row);
        return $row;
    }

    public function yesterday_info()
    {
        $this->db->select('sum(a.total) total')->from('order a');
        $this->db->join('shop b','a.shop_id = b.id','left');
        $this->db->where(array(
            'a.adate >=' => date("Y-m-d",strtotime("-1 day")),
            'a.adate <' => date("Y-m-d"),
            'a.status'=>3,
            'b.percent'=>'6'
        ));
        $per['per6'] = $this->db->get()->row_array();

        $this->db->select('sum(a.total) total')->from('order a');
        $this->db->join('shop b','a.shop_id = b.id','left');
        $this->db->where(array(
            'a.adate >=' => date("Y-m-d",strtotime("-1 day")),
            'a.adate <' => date("Y-m-d"),
            'a.status'=>3,
            'b.percent'=>'12'
        ));
        $per['per12'] = $this->db->get()->row_array();

        $this->db->select('sum(a.total) total')->from('order a');
        $this->db->join('shop b','a.shop_id = b.id','left');
        $this->db->where(array(
            'a.adate >=' => date("Y-m-d",strtotime("-1 day")),
            'a.adate <' => date("Y-m-d"),
            'a.status'=>3,
            'b.percent'=>'24'
        ));
        $per['per24'] = $this->db->get()->row_array();
        //var_dump($this->db->last_query());
        return $per;
    }

    public function jukuan(){
        $data['djk'] = $this->db->select('sum(total) total')->from('commonweal')->where('status',1)->get()->row_array();
        $data['zjjk'] = $this->db->select('total,date')->from('commonweal')
            ->where('status',2)->order_by('date','desc')->get()->row_array();
        $data['zjk'] = $this->db->select('sum(total) total')->from('commonweal')
            ->where('status',2)->get()->row_array();
        return $data;
    }

    public function phang_city(){
        $this->db->select('sum(a.total) alltotal,p.name p_name,c.name c_name')->from('shop a');
        $this->db->join('province p','p.code = a.province_code','left');
        $this->db->join('city c','c.code = a.city_code','left');
        $this->db->group_by('a.city_code');
        $this->db->where('a.total >',0);
        $this->db->order_by('alltotal','desc');
        $this->db->limit(8, 0);
        //var_dump($this->db->last_query());
        return $this->db->get()->result_array();
    }

    public function phang_company(){
        $this->db->select('a.shop_name,p.name p_name,c.name c_name,total')->from('shop a');
        $this->db->join('province p','p.code = a.province_code','left');
        $this->db->join('city c','c.code = a.city_code','left');
        $this->db->where('a.total >',0);
        $this->db->order_by('a.total','desc');
        $this->db->limit(8, 0);
        //var_dump($this->db->last_query());
        return $this->db->get()->result_array();
    }

    public function lminfo(){
        $data['lm_total'] = $this->db->select('sum(total) alltotal')->from('shop')->get()->row_array();
        $data['lm_users'] = $this->db->select('count(1) num')->from('users')->get()->row_array();
        $data['lm_shops'] = $this->db->select('count(1) num')->from('shop')->where('status',2)->get()->row_array();
        return $data;
    }

    public function shop_details($id){
        $this->db->select('a.*,p.name p_name,c.name c_name,ar.name ar_name,st.name type_name')->from('shop a');
        $this->db->join('province p','a.province_code = p.code','left');
        $this->db->join('city c','a.city_code = c.code','left');
        $this->db->join('area ar','a.area_code = ar.code','left');
        $this->db->join('shop_type st','a.type = st.id','left');
        $this->db->where('a.id',$id);
        $row = $this->db->get()->row_array();
        return $row;
    }

    public function get_goods_list($page){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('goods a');
        $this->db->join('goods_type b','a.type_id = b.id','left');
        if($this->input->post('keyword')){
            $this->db->like('a.good_name',$this->input->post('keyword'));
        }
        $this->db->where("a.flag",1);
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        $this->db->distinct('a.id');
        $this->db->select('a.*,b.type_name,c.gg_name,c.gg_old_price,c.gg_price,c.gg_kc')->from('goods a');
        $this->db->join('goods_type b','a.type_id = b.id','left');
        $this->db->join('goods_gg c','c.good_id = a.id','left');
        if($this->input->post('keyword')){
            $this->db->like('a.good_name',$this->input->post('keyword'));
        }
        $this->db->where("a.flag",1);
        $this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
        $this->db->order_by('a.id','desc');
        $this->db->order_by('c.gg_price','asc');
        $data = $this->db->get()->result_array();
        return $data;
    }

    public function good_detail($good_id){
        $this->db->select('a.*,b.type_name')->from('goods a');
        $this->db->join('goods_type b','a.type_id = b.id','left');
        $this->db->where("a.flag",1);
        $this->db->where('a.id',$good_id);
        return $this->db->get()->row_array();
    }

    public function good_gg($good_id){
        $this->db->select('*')->from('goods_gg');
        $this->db->where('good_id',$good_id);
        $this->db->order_by('gg_price','asc');
        return $this->db->get()->result_array();
    }

    public function good_pic($good_id){
        $this->db->select('*')->from('goods_pic');
        $this->db->where('good_id',$good_id);
        $this->db->order_by('id','asc');
        return $this->db->get()->result_array();
    }

    public function get_BB(){
        $this->db->select('*')->from('sys_BB');
        $this->db->where('flag',1);
        $this->db->order_by('id','desc');
        return $this->db->get()->row_array();
    }
}