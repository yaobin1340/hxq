<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/11/30
 * Time: 上午11:47
 */
class MY_APIcontroller extends CI_Controller
{
    public function __construct ()
    {
        parent::__construct();
        ini_set('date.timezone','Asia/Shanghai');
        header("Access-Control-Allow-Headers: *");
        $this->load->library('upload');
    }

    public function get_token(){
        foreach (getallheaders() as $name => $value) {
            if($name == 'Token'){
                return $value;
            }
        }
        return -1;
    }

    public function set_token_uid($uid){
        require_once (APPPATH . 'libraries/Base64.php');
        $uid = 'USER_'.$uid.'_'.time();
        //$uid = base64_encode($uid);
        $uid = base64::encrypt($uid, $this->config->item('token_key'));
        return base64_encode($uid);
    }

    public function get_token_uid($token){
        require_once (APPPATH . 'libraries/Base64.php');
        $token = base64_decode($token);
        $token = base64::decrypt($token, $this->config->item('token_key'));
        $token = explode('_', $token);
        if($token[0]!= 'USER') return 0;
        return (int)$token[1];
    }

    public function sendsms_curl($moblie,$text){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");

        curl_setopt($ch, CURLOPT_HTTP_VERSION  , CURL_HTTP_VERSION_1_0 );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD  , 'api:key-e3829a670f2c515ab8befa5096dd135c');

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $moblie,'message' => "{$text}【三客柚】"));

        $res = curl_exec( $ch );
        curl_close( $ch );
        return $res;
    }

    public function upload($folder = 'face',$input_name = 'img_input') {

            $base64 = $this->input->post($input_name);
        $name = date('Y/m/d', time());
        $dir = FCPATH . 'upload/'.$folder.'/' . $name . '/';
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)){

                $img_name = $this->getRandChar(24).'.jpg';
                $img = base64_decode(str_replace($result[1], '', $base64));
                file_put_contents($dir.$img_name, $img);//返回的是字节数
                return $name.'/'.$img_name;
            }else{
                return $this->do_upload($folder,$input_name);
            }

    }

    public function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;

        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    public function do_upload($folder = 'face',$input_name = 'img_input')
    {

        // $dataall['app_uid']=$this->app_uid;

        $name = date('Y/m/d', time());
        //$img_name = $this->getRandChar(24);
        $dir = FCPATH . 'upload/'.$folder.'/' . $name . '/';
        $config['upload_path']      = $dir;
        $config['allowed_types']    = 'gif|jpg|png';
        $config['max_size']     = 9000;
        //$config['max_width']        = 1024;
        //$config['max_height']       = 768;
        $config['file_name']       = $this->getRandChar(24);

        //$this->load->library('upload', $config);
        $this->upload->initialize($config);
       /* $dataall = array();
        $dataall['folder'] = $folder;
        $dataall['input_name'] = $input_name;
        $dataall['config'] = $config;
        $open=fopen('/var/yy.txt',"a" );
        fwrite($open,var_export($dataall,true));
        fclose($open);*/
        if ( ! $this->upload->do_upload($input_name))
        {
           /* $dataall = array();
            $dataall['file_name'] = $this->upload->display_errors();
            $open=fopen('/var/yy.txt',"a" );
            fwrite($open,var_export($dataall,true));
            fclose($open);*/
            return '';
        }
        else
        {
            /*$dataall = array();
            $dataall['file_name'] = $this->upload->data('file_name');
            $open=fopen('/var/yy.txt',"a" );
            fwrite($open,var_export($dataall,true));
            fclose($open);*/
            return $name.'/'.$this->upload->data('file_name');

        }
    }

}

/* End of file MY_APIcontroller.php */
/* Location: ./application/core/MY_APIcontroller.php */