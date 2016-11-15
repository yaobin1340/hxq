<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Frontend extends MY_Controller {

	/**
	 * Index Page for this controller.
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('frontend_model');
	}

	public function index()
	{

        $this->assign('header_name', '三客柚首页');
        $province_list = $this->frontend_model->get_province();
        $area_name = $this->frontend_model->get_area_name();
        $this->assign('area_name',$area_name?$area_name['name']:null);
        $this->assign('province_list', $province_list);
        $this->assign('city_code', $this->input->post('city_code'));
        $this->assign('shop_name', $this->input->post('t_shop_name'));
        $this->assign('area_code', $this->input->post('area_code'));
        $this->assign('province_code', $this->input->post('province_code'));
        $this->assign('lat', $this->input->post('lat'));
        $this->assign('type', $this->input->post('type'));
        $this->assign('lng', $this->input->post('lng'));
        $shop_type = $this->frontend_model->get_shop_type();
        $this->assign('shop_type', $shop_type);
		$this->display('frontend/index.html');
	}

    public function index_loaddata($page=1){
        $data = $this->frontend_model->index_loaddata($page);
        $this->assign('data', $data);
        $this->assign('page', $page);
        $this->display('frontend/index_loaddata.html');
    }

    public function register(){
        if($this->input->post()){
            if($this->input->post('yzm') != $this->session->userdata('yzm')){
                $this->show_message('验证码错误');
            }
            $this->session->set_userdata('mobile',$this->input->post('mobile'));
            $provinces = $this->frontend_model->get_province();
            $this->assign('provinces', $provinces);
            $this->display('frontend/register1.html');
        }else{
            $this->display('frontend/register.html');
        }
    }

    public function forget_page(){
        if($this->input->post()){
            if($this->input->post('yzm') != $this->session->userdata('yzm')){
                $this->show_message('验证码错误');
            }
            $this->session->set_userdata('mobile',$this->input->post('mobile'));
            $this->display('frontend/rewrite_pwd.html');
        }else{
            $this->display('frontend/forget_pwd.html');
        }
    }

	public function save_register(){
		$img = $this->upload();
		$rs = $this->frontend_model->save_register($img);
		if($rs == 1){
			$this->show_message('注册成功',site_url('user/index'));
		}else{
			$this->show_message('注册失败',site_url('frontend/register'));
		}
	}

    public function change_pwd(){
        $img = $this->upload();
        $rs = $this->frontend_model->change_pwd($img);
        if($rs == 1){
            $this->show_message('修改成功',site_url('frontend/login'));
        }else{
            $this->show_message('修改失败',site_url('frontend/forget_pwd'));
        }
    }

	public function get_yzm($mobile){
		if($this->frontend_model->check_mobile($mobile)){
			echo '{"error":-999,"msg":"手机号码已经被注册"}';
			die;
		}
		$yzm = rand(100000,999999);
		$text = '您的短信验证码是:'.$yzm;
		$this->session->set_userdata('yzm',$yzm);
		$rs = file_get_contents("http://sms-api.luosimao.com/v1/http_get/send/json?key=e3829a670f2c515ab8befa5096dd135c&mobile={$mobile}&message={$text}【拉拉秀】");
        $obj=json_decode($rs);
        if($obj->error !=0){
            $rs = $this->sendsms_curl($mobile,$text);
        }
        echo $rs;
	}

    public function get_yzm_forget($mobile){
        if(!$this->frontend_model->check_mobile($mobile)){
            echo '{"error":-999,"msg":"手机号码未被注册"}';
            die;
        }
        $yzm = rand(100000,999999);
        $text = '您的短信验证码是:'.$yzm;
        $this->session->set_userdata('yzm',$yzm);
        $rs = file_get_contents("http://sms-api.luosimao.com/v1/http_get/send/json?key=e3829a670f2c515ab8befa5096dd135c&mobile={$mobile}&message={$text}【拉拉秀】");
        $obj=json_decode($rs);
        if($obj->error !=0){
            $rs = $this->sendsms_curl($mobile,$text);
        }
        echo $rs;
    }

	public function get_city($province_code){
		$rs = $this->frontend_model->get_city($province_code);
		echo json_encode($rs);
	}
    public function get_area($city_code){
        $rs = $this->frontend_model->get_area($city_code);
        echo json_encode($rs);
    }

	public function register_shop(){
        $this->load->model('city_model');
        $this->load->model('area_model');
        $provinces = $this->frontend_model->get_province();
        $city = $this->city_model->queryContent();
        $area = $this->area_model->queryContent();
        $shop_type = $this->frontend_model->get_shop_type();
        $this->assign('provinces', $provinces);
        $this->assign('city', $city);
        $this->assign('area', $area);
        $this->assign('shop_type', $shop_type);
	    if($uid = $this->session->userdata('uid')){
	        $sessionUser = $this->frontend_model->getSessionUser($uid);
            $this->assign('sessionUser', $sessionUser);
            $this->load->model('shop_model');
            $conditionFields = array();
            $conditionFields['uid'] = $uid;
            $userShop = $this->shop_model->queryContent($conditionFields);
            if($userShop){
                $userShop = $userShop[0];
                $this->assign('shop', $userShop);
            }else{
                $this->assign('shop',array());
            }
            $this->display('frontend/register_shop.html');
        }else{
            $this->show_message('请先登陆~',site_url('/frontend/login'));
        }
	}

	public function save_register_shop(){
		$img = $this->upload('logo');
		$license = $this->upload('license','license');
		$rs = $this->frontend_model->save_register_shop($img,$license);
		if($rs == 1){
			$this->show_message('申请成功');
		}else{
			$this->show_message('申请失败');
		}
	}

    public function user_center(){
        $this->display('frontend/user_center.html');
    }

    public function login(){
        $this->display('frontend/login.html');
    }

	public function logout(){
		$this->session->sess_destroy();
		redirect(site_url('frontend/login'));
	}

	public function check_login(){
		$rs = $this->frontend_model->check_login();
		if($rs == 1){
			$this->show_message('登陆成功',site_url('user/index'));
		}else{
			$this->show_message('用户名或者密码错误');
		}
	}

    public function forget_pwd(){
        $this->display('frontend/forget_pwd.html');
    }
    public function rewrite_pwd(){
        $this->display('frontend/rewrite_pwd.html');
    }

    public function get_name_by_keywords($keywords){
        echo $this->frontend_model->get_name_by_keywords($keywords);
    }

    public function shop_details($id){
        $this->assign('header_name', '商家详情');
        $data = $this->frontend_model->shop_details($id);
        $this->assign('data', $data);
        $this->display('frontend/shop_details.html');
    }

    public function shop_list($type){
        $type_name = $this->frontend_model->get_type_name($type);
        $this->assign('header_name', $type_name?$type_name['name']:null);
        $this->assign('type', $type);
        $province_list = $this->frontend_model->get_province();
        $area_name = $this->frontend_model->get_area_name();
        $this->assign('area_name',$area_name?$area_name['name']:null);
        $this->assign('province_list', $province_list);
        $this->assign('city_code', $this->input->post('city_code'));
        $this->assign('shop_name', $this->input->post('t_shop_name'));
        $this->assign('area_code', $this->input->post('area_code'));
        $this->assign('province_code', $this->input->post('province_code'));
        $this->assign('lat', $this->input->post('lat'));
        $this->assign('lng', $this->input->post('lng'));
        $this->display('frontend/shop_list.html');
    }

    public function shop_list_loaddata($page=1){
        $data = $this->frontend_model->index_loaddata($page);
        $this->assign('data', $data);
        $this->assign('page', $page);
        $this->display('frontend/shop_list_loaddata.html');
    }

    public function show_information()
    {
        $this->assign('header_name', '数据中心');
        $this->display('frontend/show_information.html');
    }
}
