<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 17/1/3
 * Time: 下午3:07
 */
require_once "MY_APIcontroller.php";
class Apiwxpay extends MY_APIcontroller {

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
    protected $wxconfig = array();
    private $token;
    private $app_uid;
    public function __construct(){

        parent::__construct();
        $this->token = $this->get_token();
        if($this->token){
            $this->app_uid=$this->get_token_uid($this->token);
        }else{
            $this->app_uid=0;
        }
        $this->load->model('Apiwxpay_model');
        $this->load->config('wxpay_config');
        $this->wxconfig['appid']=$this->config->item('appid');
        $this->wxconfig['mch_id']=$this->config->item('mch_id');
        $this->wxconfig['apikey']=$this->config->item('apikey');
        $this->wxconfig['appsecret']=$this->config->item('appsecret');
        $this->wxconfig['sslcertPath']=$this->config->item('sslcertPath');
        $this->wxconfig['sslkeyPath']=$this->config->item('sslkeyPath');
    }

    public function test1($order_id){

        if($this->Apiwxpay_model->change_order($order_id)){
            return 'SUCCESS';
        }else{
            return 'FAIL';
        }
    }

    public function JSAPI_wxpay($order_id){

        $this->load->library('wxpay/Wechatpay',$this->wxconfig);
        $res_order = $this->Apiwxpay_model->get_order($order_id);
        if($res_order == -1){
            $rs = array(
                'success'=>false,
                'error_msg'=>'订单支付失败',
                'order_id'=>$order_id
            );
            echo json_encode($rs);
            die();
        }
        $param['body'] = '三客柚--在线支付';
        $param['attach'] = 'attach';
        $param['detail'] = "三客柚线上商城——微信支付";
        $param['out_trade_no'] = $res_order['id'];
        $param['total_fee'] = 1;
        $param["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];
        $param["time_start"] = date("YmdHis");
        $param["time_expire"] = date("YmdHis", time() + 600);
        $param["goods_tag"] = "三客柚线上商城";
        $param["notify_url"] = base_url()."/Apiwxpay/notify";
        $param["trade_type"] = "JSAPI";
        $param["openid"] = $this->session->userdata('openid');
        //统一下单，获取结果，结果是为了构造jsapi调用微信支付组件所需参数
        $result = $this->wechatpay->unifiedOrder($param);

        //如果结果是成功的我们才能构造所需参数，首要判断预支付id

        if (isset($result["prepay_id"]) && !empty($result["prepay_id"])) {
            //调用支付类里的get_package方法，得到构造的参数
            $data['parameters'] = json_encode($this->wechatpay->get_package($result['prepay_id']));
            $data['notifyurl'] = $param["notify_url"];
            $data['fee'] = 1;
            $data['pubid'] = $res_order;
            $data['orderid'] = $res_order;
            $this->load->view('wxhtml/jsapi', $data);
        }
    }

    public function APP_wxpay($order_id){
        $this->load->library('wxpay/Wechatpay',$this->wxconfig);
        $res_order = $this->Apiwxpay_model->get_order($order_id);
        if($res_order == -1){
            $rs = array(
                'success'=>false,
                'error_msg'=>'订单支付失败',
                'order_id'=>$order_id
            );
            echo json_encode($rs);
            die();
        }
        $param['body'] = '三客柚';
        $param['attach'] = 'attach';
        $param['detail'] = "三客柚线上商城——微信支付";
        $param['out_trade_no'] = $res_order['id'];
        $param['total_fee'] = 1;
        $param["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];
        $param["time_start"] = date("YmdHis");
        $param["time_expire"] = date("YmdHis", time() + 600);
        $param["goods_tag"] = "三客柚线上商城";
        $param["notify_url"] = base_url()."/Apiwxpay/notify";
        $param["trade_type"] = "APP";
        //统一下单，获取结果，结果是为了构造jsapi调用微信支付组件所需参数
        $result = $this->wechatpay->unifiedOrder($param);
        die(var_dump($result));
        //如果结果是成功的我们才能构造所需参数，首要判断预支付id

        if (isset($result["prepay_id"]) && !empty($result["prepay_id"])) {
            //调用支付类里的get_package方法，得到构造的参数
            $data['parameters'] = json_encode($this->wechatpay->get_package($result['prepay_id']));
            $data['notifyurl'] = $param["notify_url"];
            $data['fee'] = 1;
            $data['pubid'] = $res_order;
            $data['orderid'] = $res_order;
            $this->load->view('wxhtml/jsapi', $data);
        }
    }

    public function Code_wxpay($order_id){
        $res_order = $this->Apiwxpay_model->get_order($order_id);
        if($res_order == -1){
            $rs = array(
                'success'=>false,
                'error_msg'=>'1订单支付失败',
                'order_id'=>$order_id
            );
            echo json_encode($rs);
            die();
        }
        if($res_order == -2){
            $rs = array(
                'success'=>false,
                'error_msg'=>'3订单已支付',
                'order_id'=>$order_id
            );
            echo json_encode($rs);
            die();
        }
        $pay_id = $this->Apiwxpay_model->save_pay_log($order_id);
        if($pay_id<=0){
            $rs = array(
                'success'=>false,
                'error_msg'=>'1订单支付失败',
                'order_id'=>$order_id
            );
            echo json_encode($rs);
            die();
        }
        //$this->load->config('wxpay_config');
        $wxconfig['appid']=$this->config->item('appid');
        $wxconfig['mch_id']=$this->config->item('mch_id');
        $wxconfig['apikey']=$this->config->item('apikey');
        $wxconfig['appsecret']=$this->config->item('appsecret');
        $wxconfig['sslcertPath']=$this->config->item('sslcertPath');
        $wxconfig['sslkeyPath']=$this->config->item('sslkeyPath');
        $this->load->library('wxpay/Wechatpay',$wxconfig);
        $result = $this->wechatpay->getCodeUrl(
            '三客柚',
            $pay_id,
            $res_order['need_pay'],
            base_url()."Apiwxpay/notify",
            $pay_id
        );
        if($result){
           var_dump($result);
            die();
        }else{
            $rs = array(
                'success'=>false,
                'error_msg'=>'2二维码创建失败',
                'order_id'=>$order_id
            );
            echo json_encode($rs);
            die();
        }
    }

    public function notify(){
        $this->load->library('wxpay/Wechatpay',$this->wxconfig);
        $data_array = $this->wechatpay->get_back_data();
        if($data_array['result_code']=='SUCCESS' && $data_array['return_code']=='SUCCESS'){
            if($this->Apiwxpay_model->change_order($data_array['out_trade_no'])){
                return 'SUCCESS';
            }else{
                return 'FAIL';
            }
        }
    }

}