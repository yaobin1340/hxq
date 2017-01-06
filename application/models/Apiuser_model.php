<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Apiuser_model extends MY_Model
{
    protected $_tbName = 'users';
    protected $primaryKey = 'id';

    public function __construct ()
    {
    	parent::__construct();
    }

    public function find_yy($table,$id)
    {
        return $this->db->get_where($table, array('id' => $id))->row_array();
    }

    public function get_shop_flag($app_uid){
        $rs = $this->db->select('*')->from('shop')->where('uid',$app_uid)->where('status',2)->get()->row_array();
        if($rs){
            return 1;
        }else{
            return 2;
        }
    }

    public function get_user_info($app_uid){
        $this->db->select('a.*,b.name province_name,c.name city_name,d.name area_name')->from('users a');
        $this->db->join('province b','a.province_code = b.code','left');
        $this->db->join('city c','a.city_code = c.code','left');
        $this->db->join('area d','a.area_code = d.code','left');
        $this->db->where('a.id',$app_uid);
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

    public function save_information_revise($img,$app_uid){
        $data = array(
            'province_code'=>$this->input->post('province_code'),
            'city_code'=>$this->input->post('city_code'),
            'area_code'=>$this->input->post('area_code')
        );
        if($img){
            $data['face'] = $img;
        }
        $this->db->where('id',$app_uid);
        $rs = $this->db->update('users',$data);
        if($rs){
            return 1;
        }else{
            return -1;
        }
    }

    public function update_pwd($app_id){
        $row = $this->find($app_id);
        if(!$row){
            return -1;
        }
        if($row['password']==sha1($this->input->post('password'))){
            $rs = $this->db->where('id',$app_id)->update('users',array('password'=>sha1($this->input->post('new_password'))));
            if($rs){
                return 1;
            }else{
                return -3;
            }
        }else{
            return -2;
        }

    }

    public function list_orders($page,$app_uid=0)
    {
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('order_list');
        $this->db->where('uid',$app_uid);

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
        $this->db->select('a.*,shop_name,address,c.phone')->from('order_list a');
        $this->db->join('users b','a.uid=b.id','left');
        $this->db->join('shop c','a.shop_id=c.id','left');

        $this->db->where('a.uid',$app_uid);

        if($this->input->post('s_date')){
            $this->db->where("a.cdate >=",$this->input->post('s_date'));
        }

        if($this->input->post('e_date')){
            $this->db->where("a.cdate <=",$this->input->post('e_date')." 23:59:59");
        }
//        $this->db->where('shop_id',1); //TODO
        $this->db->order_by('cdate','desc');
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

    public function withdraw($app_uid){
        $data = $this->db->select()
            ->from('withdraw')
            ->where('uid',$app_uid)
            ->where('flag',1)
            ->order_by('id','desc')
            ->get()->row_array();
        return $data;
    }

    public function save_withdraw($app_uid){
        if(!$app_uid){
            return -1;
        }
        $user_info = $this->db->select()->from('users')->where('id',$app_uid)->get()->row_array();
        if((int)$this->input->post('money') > $user_info['integral']/100){
            return -2;
        }
        $data = array(
            'uid'=>$app_uid,
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
        $this->db->where('id',$app_uid);
        $this->db->set('integral',"integral - {$data['money']}",false);
        $this->db->update('users');
            $this->db->insert('money_log',array(
                'uid'=>$app_uid,
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

    public function list_withdraw_loaddata($page = 1,$app_uid){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('withdraw a');
        $this->db->where('a.uid',$app_uid);
//        $this->db->where('shop_id',1); //TODO
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件

        //获取详细列
        $this->db->select('a.*')->from('withdraw a');
        $this->db->where('a.uid',$app_uid);
        $this->db->limit($data['limit'], $offset = ($page - 1) * $data['limit']);
        $this->db->order_by('a.id','desc');
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }

    public function money_log_list_loaddata($page = 1,$app_uid){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('money_log a');
        $this->db->where('a.uid',$app_uid);
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
        $this->db->where('a.uid',$app_uid);
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

    public function user_heart_loaddata($page=1,$app_uid){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('sunflower a');
        $this->db->where('a.uid',$app_uid);
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
        $this->db->where('a.uid',$app_uid);
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
        //var_dump($this->db->last_query());
        return $data;
    }

    public function shop_heart_loaddata($page=1,$app_uid){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('sunflower_shop a');
        $this->db->join('users b','b.shop_id = a.shop_id','left');
        $this->db->where('b.id',$app_uid);
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
        $this->db->select('a.*')->from('sunflower_shop a');
        $this->db->join('users b','b.shop_id = a.shop_id','left');
        $this->db->where('b.id',$app_uid);
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
        //var_dump($this->db->last_query());
        return $data;
    }

    public function sum_count($app_uid){
        $this->db->select('count(1) num')->from('sunflower');
        $this->db->where('uid',$app_uid);
        $this->db->where('status',1);
        $row = $this->db->get()->row_array();
        return $row['num'];
    }

    public function my_income_loaddata($app_uid,$page=1){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('money_log a');
        $this->db->where('a.uid',$app_uid);
        $this->db->where('a.type <>',1);
//        $this->db->where('shop_id',1); //TODO
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件
        //获取详细列
        $this->db->select('a.*')->from('money_log a');
        $this->db->where('a.uid',$app_uid);
        $this->db->where('a.type <>',1);
        $this->db->limit($data['limit'], $offset = ($page - 1) * $data['limit']);
        $this->db->order_by('a.id','desc');
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }

    public function get_count_heart($type,$is_shop = true,$app_uid){
        if($is_shop){
            $this->db->select('count(1) num')->from('sunflower_shop a');
            $this->db->join('users b','b.shop_id = a.shop_id','left');
            $this->db->where('b.id',$app_uid);
            switch($type){
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
            $num = $this->db->get()->row();
            return $num->num;
        }else{
            $this->db->select('count(1) num')->from('sunflower');
            $this->db->where('uid',$app_uid);
            switch($type){
                case 1:
                    $this->db->where("percent",6);
                    break;
                case 2:
                    $this->db->where("percent",12);
                    break;
                case 3:
                    $this->db->where("percent",24);
                    break;

            }
            $num = $this->db->get()->row();
            return $num->num;
        }
    }

    public function my_team_user_loaddata($page=1,$app_uid){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('users a');
        $this->db->where('a.parent_id',$app_uid);
//        $this->db->where('shop_id',1); //TODO
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件
        //获取详细列
        $this->db->select('a.id,a.rel_name,a.mobile,(a.total6 + a.total12 + a.total24) team_total')->from('users a');
        $this->db->where('a.parent_id',$app_uid);
        $this->db->limit($data['limit'], $offset = ($page - 1) * $data['limit']);
        $this->db->order_by('a.id','desc');
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }

    public function my_team_shop_loaddata($page=1,$app_uid){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('shop a');
        $this->db->join('users b','b.id = a.parent_uid','left');
        $this->db->join('shop_type c','c.id = a.type','left');
        $this->db->where('a.parent_uid',$app_uid);
        $this->db->where('a.status',2);
//        $this->db->where('shop_id',1); //TODO
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件
        //获取详细列
        $this->db->select('a.shop_name,b.rel_name,c.name,total')->from('shop a');
        $this->db->join('users b','b.id = a.parent_uid','left');
        $this->db->join('shop_type c','c.id = a.type','left');
        $this->db->where('a.parent_uid',$app_uid);
        $this->db->where('a.status',2);
        $this->db->limit($data['limit'], $offset = ($page - 1) * $data['limit']);
        $this->db->order_by('a.id','desc');
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }

    public function my_team_shop2_loaddata($page=1,$app_uid){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('shop a');
        $this->db->join('users b',"b.id = a.parent_uid",'left');
        $this->db->join('shop_type c','c.id = a.type','left');
        $this->db->where('b.parent_id',$app_uid);
        $this->db->where('a.status',2);
//        $this->db->where('shop_id',1); //TODO
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件
        //获取详细列
        $this->db->select('a.shop_name,b.rel_name,c.name,total')->from('shop a');
        $this->db->join('users b',"b.id = a.parent_uid",'left');
        $this->db->join('shop_type c','c.id = a.type','left');
        $this->db->where('b.parent_id',$app_uid);
        $this->db->where('a.status',2);
        $this->db->limit($data['limit'], $offset = ($page - 1) * $data['limit']);
        $this->db->order_by('a.id','desc');
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }

    public function save_register_shop($app_uid,$imgs){
        if(!($uid = $app_uid)){
            return -1;
        }
        $data = array(
            'uid'=>$app_uid,
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
            'license'=>$imgs['license']?$imgs['license']:'',
            'cdate'=>date('Y-m-d H:i:s',time()),
            'logo'=>$imgs['logo']?$imgs['logo']:'',
            'cns1'=>$imgs['cns1']?$imgs['cns1']:'',
//            'cns2'=>$imgs['cns2']?$imgs['cns2']:'',
            'sfz1'=>$imgs['sfz1']?$imgs['sfz1']:'',
//            'sfz2'=>$imgs['sfz2']?$imgs['sfz2']:'',
            'status'=>1,//待审核
            'percent'=>$this->input->post('percent'),
        );
        if(!$imgs['logo'])unset($data['logo']);
        if(!$imgs['license'])unset($data['license']);
        if(!$imgs['cns1'])unset($data['cns1']);
        if(!$imgs['cns2'])unset($data['cns2']);
        if(!$imgs['sfz1'])unset($data['sfz1']);
        if(!$imgs['sfz2'])unset($data['sfz2']);

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

        if($data['parent_uid']==$app_uid){
            return -2;
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

    public function add_cart($app_uid){
        $data = array(
            'good_id'=>$this->input->post('good_id'),
            'gg_id'=>$this->input->post('gg_id'),
            'uid'=>$app_uid
        );
        $row = $this->db->select()->from('user_cart')->where($data)->get()->row_array();
        if($row){
            return -2;
        }
        $data['cdate']=date('Y-m-d H:i:s');
        $data['num']=1;
        $res = $this->db->insert('user_cart',$data);
        if($res){
            return 1;
        }else{
            return -1;
        }
    }

    public function list_cart($app_uid,$page){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('user_cart a');
        $this->db->join('goods b',"b.id = a.good_id",'inner');
        $this->db->join('goods_gg c','c.id = a.gg_id','inner');
        $this->db->where('b.flag',1);
        $this->db->where('a.uid',$app_uid);
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件
        //获取详细列
        $this->db->select('a.id cart_id,a.num,a.cdate cart_cdate,b.*,c.gg_name,c.gg_kc,c.gg_old_price,c.gg_price')->from('user_cart a');
        $this->db->join('goods b',"b.id = a.good_id",'inner');
        $this->db->join('goods_gg c','c.id = a.gg_id','inner');
        $this->db->where('b.flag',1);
        $this->db->where('a.uid',$app_uid);
        $this->db->limit($data['limit'], $offset = ($page - 1) * $data['limit']);
        $this->db->order_by('a.id','desc');
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }

    public function change_cart($app_uid,$cart_id,$num){
        $res = $this->db->where(array(
            'uid'=>$app_uid,
            'id'=>$cart_id,
        ))->update('user_cart',array('num'=>$num));
        if($res){
            return 1;
        }else{
            return -1;
        }

    }

    public function delete_cart($app_uid,$cart_id){
        $res = $this->db->where(array(
            'uid'=>$app_uid,
            'id'=>$cart_id,
        ))->delete('user_cart');
        if($res){
            return 1;
        }else{
            return -1;
        }
    }

    public function add_address($app_uid){
        $data=array(
            'address'=>$this->input->post('address'),
            'person'=>$this->input->post('person'),
            'phone'=>$this->input->post('phone'),
            'zip'=>$this->input->post('zip'),
            'uid'=>$app_uid
        );
        if($this->input->post('default')==1){
            $this->db->where('uid',$app_uid)->update('user_address',array('default'=>-1));
            $data['default']=1;
        }
        $res = $this->db->insert('user_address',$data);
        if($res){
            return 1;
        }else{
            return -1;
        }
    }

    public function edit_address($app_uid,$address_id){
        $data=array(
            'address'=>$this->input->post('address'),
            'person'=>$this->input->post('person'),
            'phone'=>$this->input->post('phone'),
            'zip'=>$this->input->post('zip'),
            'default'=>-1
        );
        if($this->input->post('default')==1){
            $this->db->where('uid',$app_uid)->update('user_address',array('default'=>-1));
            $data['default']=1;
        }
        //die(var_dump($this->db->last_query()));
        $res = $this->db->where(array(
            'uid'=>$app_uid,
            'id'=>$address_id
        ))->update('user_address',$data);

        if($res){
            return 1;
        }else{
            return -1;
        }
    }

    public function delete_address($app_uid,$address_id){
        $res = $this->db->where(array(
            'uid'=>$app_uid,
            'id'=>$address_id
        ))->delete('user_address');
        if($res){
            return 1;
        }else{
            return -1;
        }

    }

    public function default_address($app_uid,$address_id){
        $data=array(
            'default'=>-1
        );
        if($this->input->post('default')==1){
            $this->db->where('uid',$app_uid)->update('user_address',array('default'=>-1));
            $data['default']=1;
        }
        $res = $this->db->where(array(
            'uid'=>$app_uid,
            'id'=>$address_id
        ))->update('user_address',$data);
        if($res){
            return 1;
        }else{
            return -1;
        }
    }

    public function list_address($app_uid,$page){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(1) num')->from('user_address a');
        $this->db->where('a.uid',$app_uid);
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件
        //获取详细列
        $this->db->select('a.*')->from('user_address a');
        $this->db->where('a.uid',$app_uid);
        $this->db->limit($data['limit'], $offset = ($page - 1) * $data['limit']);
        $this->db->order_by('a.id','desc');
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }

    public function get_def_address($app_uid){
        $this->db->select()->from('user_address');
        $this->db->where('uid',$app_uid);
        $this->db->where('default',1);
        return $this->db->get()->row_array();
    }

    public function address_info($address_id){
        /*$this->db->select('a.*,b.name province_name,c.name city_name,d.name area_name')->from('user_address a');
        $this->db->join('province b','a.province_code = b.code','left');
        $this->db->join('city c','a.city_code = c.code','left');
        $this->db->join('area d','a.area_code = d.code','left');
        $this->db->where('a.id',$address_id);
        return $this->db->get()->row_array();*/
        $this->db->select()->from('user_address a');
        $this->db->where('a.id',$address_id);
        return $this->db->get()->row_array();
    }

    public function save_orderByCart($app_uid){
       /* $address = $this->db->select()->from('user_address')->where(array(
            'id'=>$this->input->post('address_id'),
            'uid'=>$app_uid
        ))->get()->row_array();
        if(!$address){
            return -2;
        }*/
        $cart_ids = $this->input->post('cart_ids');

        if(!is_array($cart_ids)){
            $arr = explode(",",$cart_ids);
            if(!is_array($arr)){
                return -3;
            }else{
                $cart_ids = $arr;
            }
        }
        $this->db->trans_start();
        //先建立主订单
        $data = array(
            'uid'=>$app_uid,
            'cdate'=>date('Y-m-d H:i:s'),
            'pay_code'=>$this->input->post('pay_code'),
            'remark'=>$this->input->post('remark',true)
        );
        $this->db->insert('user_order',$data);
        $order_id = $this->db->insert_id();
        $total_price = 0;
        //处理商品
        foreach ($cart_ids as $key => $val) {
            $good_info = $this->db->select('b.*,a.num cart_num,c.id gg_id,c.gg_name,c.gg_price')->from('user_cart a')
                ->join('goods b','a.good_id = b.id','inner')
                ->join('goods_gg c','a.gg_id = c.id','inner')
                ->where('a.uid',$app_uid)
                ->where('c.gg_kc >',0)
                ->where('a.id',(int)$val)->get()->row_array();

            if($good_info){
                $order_detail = array(
                    'uo_id'=>$order_id,
                    'good_name'=>$good_info['good_name'],
                    'good_logo'=>$good_info['logo'],
                    'good_id'=>$good_info['id'],
                    'gg_id'=>$good_info['gg_id'],
                    'gg_name'=>$good_info['gg_name'],
                    'good_unit'=>$good_info['unit'],
                    'good_demo'=>$good_info['demo'],
                    'good_gmxz'=>$good_info['gmxz'],
                    'good_price'=>$good_info['gg_price'],
                    'good_num'=>$good_info['cart_num'],
                );
                $detail_res = $this->db->insert('user_order_detail',$order_detail);
                if($detail_res){
                    $total_price+=(int)$good_info['gg_price']*(int)$good_info['cart_num'];
                }
                $this->db->where(array(
                    'uid'=>$app_uid,
                    'id'=>(int)$val
                ))->delete('user_cart');//删除购物车信息
            }
        }

        //最后保存主订单需要支付的金额,且 如果金额为0 则将订单状态改为-1
        if($total_price==0){
            $this->db->where(array(
                'id'=>$order_id,
                'uid'=>$app_uid
            ))->update('user_order',array(
                'status'=>-1
            ));
        }else{
            $update_data=array(
                'total_price' => $total_price,
                'need_pay' => $total_price,
                'use_integral' => 0
            );
            $this->db->where(array(
                'id'=>$order_id,
                'uid'=>$app_uid
            ))->update('user_order',$update_data);
        }
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            if($total_price==0){
                return -4;
            }
            return $order_id;
        }

    }

    public function get_orderGoods($order_id){
        $this->db->select()->from('user_order_detail');
        $this->db->where('uo_id',$order_id);
        return $this->db->get()->result_array();
    }

    public function get_orderAddress($order_id){
        $this->db->select()->from('user_order_address');
        $this->db->where('uo_id',$order_id);
        return $this->db->get()->row_array();
    }

    public function save_orderByPay($app_uid){
        $order_address = $this->db->select()->from('user_order_address')->where(array(
            'uo_id'=>$this->input->post('order_id')
        ))->get()->row_array();
        if(!$order_address){
            if(!trim($this->input->post('address_id'))){
               return -6;
            }
            $address = $this->db->select()->from('user_address')->where(array(
                'id'=>$this->input->post('address_id'),
                'uid'=>$app_uid
            ))->get()->row_array();
            if(!$address){
                return -2;
            }
            $this->db->insert('user_order_address',array(
                'uo_id'=>$this->input->post('order_id'),
                'address'=>$address['address'],
                'zip'=>$address['zip'],
                'person'=>$address['person'],
                'phone'=>$address['phone']
            ));
        }else{
            if($this->input->post('address_id')){
                $address = $this->db->select()->from('user_address')->where(array(
                    'id'=>$this->input->post('address_id'),
                    'uid'=>$app_uid
                ))->get()->row_array();
                if($address){
                    $this->db->where('uo_id',$this->input->post('order_id'))->delete('user_order_address');
                    $this->db->insert('user_order_address',array(
                        'uo_id'=>$this->input->post('order_id'),
                        'address'=>$address['address'],
                        'zip'=>$address['zip'],
                        'person'=>$address['person'],
                        'phone'=>$address['phone']
                    ));
                }
            }
        }

        $order_info = $this->db->select()->from('user_order')->where(array(
            'id'=>$this->input->post('order_id'),
            'uid'=>$app_uid
        ))->get()->result_array();
        if(!$order_info){
            return -4;
        }
        if(!$order_info['status']!=1){
            return -5;
        }
        $old_total_price = (int)$order_info['total_price'];
        $old_total_integral = (int)$order_info['user_integral'];
        $new_total_price = 0;
        $update_data = array(
            'remark'=>$this->input->post('remark',true),
            'pay_code'=>$this->input->post('pay_code')
        );
        $order_id = $this->input->post('order_id');
        //校验是否 商品都不存在
        $this->db->select('a.*')->from('user_order_detail a');
        $this->db->join('goods_gg b','b.id = a.gg_id','inner');
        $this->db->join('goods c','c.id = b.good_id','inner');
        $this->db->where(array(
            'a.uo_id'=>$order_id,
            'b.gg_kc >'=>0
        ));
        $goods_list = $this->db->get()->result_array();
        if(!$goods_list){
            $this->db->where(array(
                'id'=>$order_id,
                'uid'=>$app_uid
            ))->update('user_order',array(
                'status'=>-1
            ));
            $this->db->where('id',$app_uid);
            $this->db->set('integral',"integral + {$old_total_integral}",false);
            $this->db->update('users');
            $this->db->insert('money_log',array(
                'remark'=>'订单失败返回 葵花籽',
                'money'=>$old_total_integral,
                'type'=>12,
                'uid'=>$app_uid,
                'cdate' => date('Y-m-d H:i:s')
            ));
            return -3;
        }
        $this->db->trans_start();
        //先建立主订单

        //保存订单地址
        //$order_address = $this->db->select()->from('user_order_address')



        //处理商品
        foreach ($goods_list as $key => $val) {
            $new_total_price+=(int)$val['good_price']*(int)$val['good_num'];
        }

        //最后保存主订单需要支付的金额,且 如果金额为0 则将订单状态改为-1
        if($new_total_price <= 0){
            $this->db->where(array(
                'id'=>$order_id,
                'uid'=>$app_uid
            ))->update('user_order',array(
                'status'=>-1
            ));
            $this->db->where('id',$app_uid);
            $this->db->set('integral',"integral + {$old_total_integral}",false);
            $this->db->update('users');
            $this->db->insert('money_log',array(
                'remark'=>'订单返回 葵花籽',
                'money'=>$old_total_integral,
                'type'=>12,
                'uid'=>$app_uid,
                'cdate' => date('Y-m-d H:i:s')
            ));
        }else{
            //这里判断金额
            $user_info = $this->db->select('integral')->from("users")->where("id",$app_uid)->get()->row_array();
            if(!$user_info){
                return -1;
            }

            if($old_total_integral==0){
                $use_integral = $this->input->post('use_integral');
                $use_integral = $use_integral ? $use_integral : 0;
                $use_integral = (int)($use_integral*100);
                if($new_total_price < $use_integral){
                    $use_integral = $new_total_price;
                }
                if($user_info['integral']>=$use_integral){
                    $this->db->where('id',$app_uid);
                    $this->db->set('integral',"integral - {$use_integral}",false);
                    $this->db->update('users');
                    $this->db->insert('money_log',array(
                        'remark'=>'线上商城购物 扣除葵花籽',
                        'money'=>$use_integral,
                        'type'=>11,
                        'uid'=>$app_uid,
                        'cdate' => date('Y-m-d H:i:s')
                    ));
                    $update_data['use_integral']=$use_integral;
                    $update_data['need_pay']=$new_total_price - $use_integral;
                }else{
                    $this->db->where('id',$app_uid);
                    $this->db->set('integral',"integral - {$user_info['integral']}",false);
                    $this->db->update('users');
                    $this->db->insert('money_log',array(
                        'remark'=>'线上商城购物 扣除葵花籽',
                        'money'=>$user_info['integral'],
                        'type'=>11,
                        'uid'=>$app_uid,
                        'cdate' => date('Y-m-d H:i:s')
                    ));
                    $update_data['use_integral']=$user_info['integral'];
                    $update_data['need_pay']=$new_total_price - $user_info['integral'];
                }

            }else{
                $use_integral = $old_total_integral;
                if($use_integral > $new_total_price){
                    $tuihuan = $use_integral - $old_total_price;
                    $this->db->where('id',$app_uid);
                    $this->db->set('integral',"integral + {$tuihuan}",false);
                    $this->db->update('users');
                    $this->db->insert('money_log',array(
                        'remark'=>'订单返回 葵花籽',
                        'money'=>$tuihuan,
                        'type'=>12,
                        'uid'=>$app_uid,
                        'cdate' => date('Y-m-d H:i:s')
                    ));
                    $use_integral = $new_total_price;
                }
                $update_data['total_price'] = $new_total_price;
                $update_data['use_integral']=$use_integral;
                $update_data['need_pay']=$new_total_price - $use_integral;
            }

            if($update_data['need_pay'] == 0){
                $update_data['status']=3;
            }
            if($update_data['need_pay'] < 0){
                $update_data['status']=-1;
            }
            if($update_data['need_pay'] > 0){
                $update_data['status']=2;
            }
            $this->db->where(array(
                'id'=>$order_id,
                'uid'=>$app_uid
            ))->update('user_order',$update_data);

        }
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            if($new_total_price==0){
                return -4;
            }
            return $order_id;
        }
    }

    public function save_orderByGood($app_uid){
        $address = $this->db->select()->from('user_address')->where(array(
            'id'=>$this->input->post('address_id'),
            'uid'=>$app_uid
        ))->get()->row_array();
        if(!$address){
            return -2;
        }
        $good_info = $this->db->select('a.*,c.id gg_id,c.gg_name,c.gg_price,c.gg_kc')->from('goods a')
            ->join('goods_gg c','a.id = c.good_id','inner')
            ->where('a.id',$this->input->post('good_id'))
            ->where('c.id',$this->input->post('gg_id'))->get()->row_array();
        if(!$good_info){
            return -3;
        }
        if($good_info['gg_kc'] <=0){
            return -4;
        }
        $total_price = $good_info['gg_price']*$this->input->post('num');
        if($total_price<=0){
            return -5;
        }

        $use_integral = $this->input->post('use_integral');
        $use_integral = $use_integral ? $use_integral : 0;
        $use_integral = (int)($use_integral*100);
        if($use_integral >=$total_price){
            $use_integral = $total_price;
        }
        $this->db->trans_start();
        //一,先建立主订单
        $data = array(
            'uid'=>$app_uid,
            'cdate'=>date('Y-m-d H:i:s'),
            'remark'=>$this->input->post('remark',true),
            'total_price'=>$total_price,
            'need_pay'=>$total_price
        );
        //1,获取需要第三方支付的费用
        $user_info = $this->db->select('integral')->from("users")->where("id",$app_uid)->get()->row_array();
        if(!$user_info){
            return -1;
        }
        if($user_info['integral']>=$use_integral){
            $this->db->where('id',$app_uid);
            $this->db->set('integral',"integral - {$use_integral}",false);
            $this->db->update('users');
            $this->db->insert('money_log',array(
                'remark'=>'向日葵激励(会员)',
                'money'=>$use_integral,
                'type'=>11,
                'uid'=>$app_uid,
                'cdate' => date('Y-m-d H:i:s')
            ));
            $data['use_integral']=$use_integral;
            $data['need_pay']=$total_price - $use_integral;
        }else{
            $this->db->where('id',$app_uid);
            $this->db->set('integral',"integral - {$user_info['integral']}",false);
            $this->db->update('users');
            $this->db->insert('money_log',array(
                'remark'=>'向日葵激励(会员)',
                'money'=>$user_info['integral'],
                'type'=>11,
                'uid'=>$app_uid,
                'cdate' => date('Y-m-d H:i:s')
            ));
            $data['use_integral']=$user_info['integral'];
            $data['need_pay']=$total_price - $user_info['integral'];
        }
        if($data['need_pay'] == 0){
            $data['status']=2;
        }
        //2,保存主订单
        $this->db->insert('user_order',$data);
        $order_id = $this->db->insert_id();
        //保存订单地址
        $this->db->insert('user_order_address',array(
            'uo_id'=>$order_id,
            'address'=>$address['address'],
            'zip'=>$address['zip'],
            'person'=>$address['person'],
            'phone'=>$address['phone']
        ));
        $order_detail = array(
            'uo_id'=>$order_id,
            'good_name'=>$good_info['good_name'],
            'good_logo'=>$good_info['logo'],
            'good_id'=>$good_info['id'],
            'gg_id'=>$good_info['gg_id'],
            'gg_name'=>$good_info['gg_name'],
            'good_unit'=>$good_info['unit'],
            'good_demo'=>$good_info['demo'],
            'good_gmxz'=>$good_info['gmxz'],
            'good_price'=>$good_info['gg_price'],
            'good_num'=>$this->input->post('num'),
        );
        $this->db->insert('user_order_detail',$order_detail);
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return $order_id;
        }

    }

    public function user_order_list($app_uid,$page){
        $data['limit'] = $this->limit;
        //获取总记录数
        $this->db->select('count(distinct(a.id)) as num')->from('user_order a');
        $this->db->join('user_order_detail b',"b.uo_id = a.id",'inner');
        $this->db->where('a.status >',0);
        $this->db->where('a.uid',$app_uid);
        $num = $this->db->get()->row();
        $data['total'] = $num->num;

        //搜索条件
        //获取详细列
        $this->db->select('a.*,b.good_logo,sum(b.good_num)')->from('user_order a');
        $this->db->join('user_order_detail b',"b.uo_id = a.id",'inner');
        $this->db->where('a.status >',0);
        $this->db->where('a.uid',$app_uid);
        $this->db->limit($data['limit'], $offset = ($page - 1) * $data['limit']);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id','desc');
        $data['items'] = $this->db->get()->result_array();

        return $data;
    }
}