<?php
    namespace HU7\lib\framework;

    use HU7\lib\DbConfig;
    use HU7\lib\Db;

    abstract class BaseDataService {
        protected static $_db;
        protected $_tbName;

        function __construct() {
            if (!self::$_db instanceof Db) {
                self::$_db = new Db(DbConfig::getConf());
                self::$_db->connect();
            }
        }

        function getTbName() {
            return $this->_tbName;
        }

        function getFields() {
            return $this->fields;
        }

        function autocommit($flag = FALSE) {
            return self::$_db->autocommit($flag);
        }

        function commit() {
            return self::$_db->commit();
        }

        function rollback() {
            return self::$_db->rollback();
        }

        function count($condtionFields = null) {
            $sql = $this->_getCountSql($this->_tbName, $condtionFields);
            $res = self::$_db->select($sql);
            if (isset($res[0])) {
                return intval($res[0]['num']);
            } else {
                return NULL;
            }
        }

        protected function _getCountSql($tbName, $condtionFields = null) {
            $where = $this->_getWhere($condtionFields);
            $sql   = sprintf("SELECT count(1) as num FROM %s WHERE %s", $tbName, $where);

            return $sql;
        }

        protected function _getWhere($condtionFields = null) {
            $where = ' 1=1 ';

            if (is_array($condtionFields)) {

                foreach ($condtionFields as $tbField => $tbVal) {
                    $where .= (" AND `$tbField` = '$tbVal' ");
                }
            } else if ($condtionFields) {
                $where = $condtionFields;
            }

            return $where;
        }

        protected function _getJoinWhere($tbNameA,$tbNameB,$condtionFieldsA = null,$condtionFieldsB = null) {
            $where = ' 1=1 ';
            if (is_array($condtionFieldsA)) {
                foreach ($condtionFieldsA as $tbField => $tbVal) {
                    $where .= (" AND $tbNameA.`$tbField` = '$tbVal' ");
                }
            }
            if (is_array($condtionFieldsB)) {
                foreach ($condtionFieldsB as $tbField => $tbVal) {
                    $where .= (" AND $tbNameB.`$tbField` = '$tbVal' ");
                }
            }

            return $where;
        }

        protected function _getSelectSql($tbName, $condtionFields = null, $selectFields = '*') {
            $where  = $this->_getWhere($condtionFields);
            $select = $this->_getSelectFields($selectFields);
            $sql    = sprintf("SELECT %s FROM %s WHERE %s", $select, $tbName, $where);

            return $sql;
        }

        protected function _getJoinSelectSql($tbNameA,$tbNameB,$joinConditionField,$condtionFieldsA = null,$condtionFieldsB = null, $selectFieldsA = '*',$selectFieldsB = '*'){
            $joinCondition = "$tbNameA.`$joinConditionField` = $tbNameB.`$joinConditionField`";
            $select = $this->_getJoinSelectFields($tbNameA,$tbNameB,$selectFieldsA,$selectFieldsB);
            $where  = $this->_getJoinWhere($tbNameA,$tbNameB,$condtionFieldsA,$condtionFieldsB);
            $sql    = sprintf("SELECT %s FROM %s INNER JOIN %s ON %s WHERE %s",$select,$tbNameA,$tbNameB,$joinCondition,$where);

            return $sql;
        }

        protected function _getSelectFields($selectFields = '*') {
            if (is_array($selectFields)) {
                return "`" . implode("`,`", $selectFields) . "`";
            } else {
                return $selectFields;
            }
        }

        protected function _getJoinSelectFields($tbNameA,$tbNameB,$selectFieldsA = '*',$selectFieldsB = '*') {
            if (is_array($selectFieldsA)) {
                if($selectFieldsA){
                    $selectA = "$tbNameA.`" . implode("`,$tbNameA.`", $selectFieldsA) . "`";
                }else{
                    $selectA = '';
                }
            } else {
                if($selectFieldsA == '*' || $selectFieldsA == null){
                    $selectA = "$tbNameA.*";
                }else{
                    $selectA = $selectFieldsA;
                }
            }

            if (is_array($selectFieldsB)) {
                if($selectFieldsB){
                    $selectB = "$tbNameB.`" . implode("`,$tbNameB.`", $selectFieldsB) . "`";
                }else{
                    $selectB = '';
                }
            } else {
                if($selectFieldsB == '*' || $selectFieldsB == null){
                    $selectB = "$tbNameB.*";
                }else{
                    $selectB = $selectFieldsB;
                }
            }

            $selectFields = $selectA ? $selectA : '';
            $selectFields .= $selectB ? ',' . $selectB : '';
            return $selectFields;
        }

        protected function _getUpdateSql($tbName, $arrFields, $condtionFields = null) {
            $where      = $this->_getWhere($condtionFields);
            $Field2Vals = null;
            foreach ($arrFields as $key => $val) {
                $Field2Vals[] = sprintf("`%s`='%s'", self::$_db->real_escape_string($key), self::$_db->real_escape_string($val));
            }
            $fields = array_keys($arrFields);
            $vals   = array_values($arrFields);

            $sql = sprintf("UPDATE `%s` SET %s WHERE %s", $tbName, implode(",", $Field2Vals), $where
            );

            return $sql;
        }

        protected function _getInsertSql($tbName, $arrFields) {
            $fields = array_keys($arrFields);
            $vals   = array_values($arrFields);
            foreach ($vals as $k => $v)
                $vals[$k] = self::$_db->real_escape_string($v);

            $sql = sprintf("INSERT INTO `%s`(`%s`) VALUES ('%s')", $tbName, implode("`,`", $fields), implode("','", $vals)
            );

            return $sql;
        }

    }
