<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "MY_APIcontroller.php";
class Apiweixin extends MY_APIcontroller {

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
	public function __construct(){
		parent::__construct();
		$this->token = $this->get_token();
		if($this->token){
			$this->app_uid=$this->get_token_uid($this->token);
		}else{
			$this->app_uid=0;
		}
	}

	public function buildWxData($suffix){
		$signPackage = $this->wxjssdk->wxgetSignPackage($suffix);
		//变量
		$data = array(
			'success'=>true,
			'error_msg'=>'',
			'wxappId'=>$signPackage["appId"],
			'wxtimestamp'=>$signPackage["timestamp"],
			'wxnonceStr'=>$signPackage["nonceStr"],
			'wxsignature'=>$signPackage["signature"]
		);
		echo json_encode($data);
		die();
	}
}
