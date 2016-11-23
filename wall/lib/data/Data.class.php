<?php

namespace HU7\lib\data;

use HU7\lib\framework\BaseDataService;

class Data extends BaseDataService
{

    function select($sql){
        return self::$_db->select($sql);
    }

    function write($sql){
        self::$_db->write($sql);
        return self::$_db->insert_id();
    }

    function queryContent($table,$condtionFields = null, $selectFields = '*')
    {
        $sql = $this->_getSelectSql($table, $condtionFields, $selectFields);

        return self::$_db->select($sql);
    }

    function add($table,array $arrFields)
    {
        $sql = $this->_getInsertSql($table, $arrFields);
        self::$_db->write($sql);

        return self::$_db->insert_id();
    }

}

?>
