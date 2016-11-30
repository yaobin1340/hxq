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
}