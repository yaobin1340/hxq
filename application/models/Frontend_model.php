<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Frontend_model extends MY_Model
{
    public function __construct ()
    {
    	parent::__construct();
    }

    public function get_city($province_code = null){
        $this->db->select()->from('city');
        if($province_code) $this->db->where('provincecode',$province_code);
        return $this->db->get()->result_array();
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
            'openid'=>$this->session->userdata('openid'),
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
            'baidu_lat'=>$this->input->post('baidu_lat')?$this->input->post('baidu_lat'):$this->input->post('lat'),
            'baidu_lng'=>$this->input->post('baidu_lng')?$this->input->post('baidu_lng'):$this->input->post('lng'),
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
            $this->update_openid($rs->id,$this->session->userdata('openid'));
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

    public function index_loaddata($page=1){
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

        //搜索条件
        $data['province_code'] = $this->input->post('province_code')?$this->input->post('province_code'):null;
        $data['city_code'] = $this->input->post('city_code')?$this->input->post('city_code'):null;
        $data['area_code'] = $this->input->post('area_code')?$this->input->post('area_code'):null;
        $data['type'] = $this->input->post('type')?$this->input->post('type'):null;
        $data['lat'] = $this->input->post('lat')?$this->input->post('lat'):0;
        $data['lng'] = $this->input->post('lng')?$this->input->post('lng'):0;
        $data['shop_name'] = $this->input->post('shop_name')?$this->input->post('shop_name'):null;
        //获取详细列
        $this->db->select("*,
        ROUND(lat_lng_distance({$data['lat']}, {$data['lng']}, lat, lng), 2) AS juli,
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
        if($this->input->post('area_code')){
            $this->db->where('id',$this->session->userdata('uid'))
                ->update('users',array('u_area_code'=>$this->input->post('area_code')));
           // echo $this->db->last_query();
        }
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

    public function get_area_name($code){
        return $this->db->select('name')->from('area')->where('code',$code)->get()->row_array();
    }

    public function get_type_name($type){
        return $this->db->select('name')->from('shop_type')->where('id',$type)->get()->row_array();
    }

    public function nearcity($area_name){
        return $this->db->select('name,code')->from('area')->where('name',$area_name)->get()->row_array();
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
        $this->db->select();
        $this->db->from('settlement');
        $this->db->where('date',date("Y-m-d",strtotime("-1 day")));
        $this->db->order_by('date','desc');
        $row = $this->db->get()->row_array();
        //var_dump($row);
        return $row;
    }
}