<?php

namespace HU7\config;


//date_default_timezone_set("Asia/Shanghai");
//define("ENV_TYPE_ONLINE", "ENV_TYPE_ONLINE");
//define("ENV_TYPE_LOCAL", "ENV_TYPE_LOCAL");
define("ROOT", realpath(dirname(__FILE__) . "/../").'/');
//define("EXT_ROOT", ROOT . "ext/");
//define("CACHE_ROOT", ROOT . "../../tmpData/");
//define("IMG_DIR", ROOT . '../../images/');//不能用realpath，如果路径不存在，会返回异常路径
//define("IMG_URL", 'http://img.7hu.cn/');
define("NAMESPACE_PREFIX", "HU7\\");


//define("ENV_TYPE", ENV_TYPE_LOCAL);

function autoload_namespace($className) {
    if (strpos($className, "\\") === FALSE) return;
    $className = str_ireplace( NAMESPACE_PREFIX,'',$className);
    $className = str_ireplace( "\\",'/',$className);

    //根据“_”线分割类名,作为类路径
    //1.对文件夹名转换为全小写，类文件名保持不变
    $pathInfo = explode("_", $className);
    $fileName = $pathInfo[count($pathInfo) - 1];
    foreach ($pathInfo as $k => $v) {
        $pathInfo[$k] = strtolower($v);
    }

    $pathInfo[count($pathInfo) - 1] = $fileName;
    $partPath                       = ROOT . implode($pathInfo, '/');
    //echo 'clsname:' . $className . ':' . $partPath . "</br>\n";
    if (file_exists($partPath . '.class.php')) {
        require_once($partPath . '.class.php');
        return;
    }

    //echo  $partPath.'</br>';
    if (file_exists($partPath . '.php')) {
        require_once($partPath . '.php');
        return;
    }
}

spl_autoload_register(__NAMESPACE__ . '\autoload_namespace');

