<?php
namespace HU7\lib;

class DbConfig
{
	static function getConf()
	{
		return self::_getConf();
	}

	private static function _getConf()
	{
		$db_config['charset'] = 'utf8';
		$db_config['r']['name'] =  'wall';
		$db_config['r']['user'] =  'root';
		//$db_config['r']['pass'] = '';
		$db_config['r']['pass'] = 'divcssphp1634';
		//$db_config['r']['host'] = '127.0.0.1';
		$db_config['r']['host'] = '127.0.0.1';
		$db_config['r']['port'] = 3306;

	
		$db_config['w']['name'] =  'wall';
		$db_config['w']['user'] =  'root';
		//$db_config['w']['pass'] = '';
		$db_config['w']['pass'] = 'divcssphp1634';
		//$db_config['w']['host'] = '127.0.0.1';
		$db_config['w']['host'] = '127.0.0.1';
		$db_config['w']['port'] = 3306;	

		return $db_config;
	}
	
}

?>
