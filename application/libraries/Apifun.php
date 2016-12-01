<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Apifun {
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
        $uid = base64::encrypt($uid, 'abcd888888');
        return base64_encode($uid);
    }

    public function get_token_uid($token){
        require_once (APPPATH . 'libraries/Base64.php');
        $token = base64_decode($token);
        $token = base64::decrypt($token, 'abcd888888');
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

    /**
     * 判断输入的字符串是否是一个合法的手机号(仅限中国大陆)
     *
     * @param string $string
     * @return boolean
     */
    function isMobile($string) {
        if(preg_match('/^[1]+[3,4,5,7,8]+\d{9}$/', $string))
            return true;
        return false;
        //return ctype_digit($string) && (11 == strlen($string)) && ($string[0] == 1);
    }
}