<?php
@header("Content-type: text/html; charset=utf-8");
 
define("TOKEN", "wall"); //配置API
//define("Web_ROOT",'http://weixin.coolwb.com/icoolwb'); //代码当前目录域名
define("Web_ROOT",'http://sky.asmzs.com/wall'); //代码当前目录域名
$weixin_name = '我们！';//这里可以配置你的微信公众账号名字，你也可以改下面的源码
$coolwb_wxh = 'icoolwb';//微信帐号（wall前台显示）
$weixin_wxq = Web_ROOT.'/wall/';
$huati='';//话题内容 如：#我爱你#

//		/*链接数据库*/
//	   $dbname = 'wall';//数据库的名称
//       $host = "127.0.0.1";
//       $port = "80";
//       $user = "root";
//       $pwd = "divcssphp1634";
//
//       /*接着调用mysql_connect()连接服务器*/
//        $link = @mysqli_connect("{$host}:{$port}",$user,$pwd,true);
//       if(!$link) {
//                   die("Connect Server Failed: " . mysql_error($link));
//                  }
//       /*连接成功后立即调用mysql_select_db()选中需要连接的数据库*/
//       if(!mysqli_select_db($dbname,$link)) {
//                   die("Select Database Failed: " . mysql_error($link));
//                  }
//		mysqli_query("SET NAMES UTF8");
////以上连接数据库
?>