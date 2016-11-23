<?php

namespace HU7\lib;

use mysqli;

/**
 * @desc : 鏁版嵁搴撴搷浣滃熀绫� 淇濊瘉鍚屼竴涓暟鎹簱鍙湁涓�釜杩炴帴瀹炰緥瀛樺湪
 * @author : yangxiongnan
 */
class Db {

    const NODE_READ  = 'r';
    const NODE_WRITE = 'w';

    private $r              = null; //璇诲簱杩炴帴瀹炰緥
    private $w              = null; //鍐欏簱杩炴帴瀹炰緥
    private $_config        = array();
    private $_debug         = FALSE;
    private $_character_set = 'latin1';

    public function __construct($config = array()) {
        $this->_config['charset'] = $config['charset'];

        $this->_config ['r'] ['host'] = $config ['r'] ['host'];
        $this->_config ['r'] ['name'] = $config ['r'] ['name'];
        $this->_config ['r'] ['user'] = $config ['r'] ['user'];
        $this->_config ['r'] ['pass'] = $config ['r'] ['pass'];
        $this->_config ['r'] ['port'] = $config ['r'] ['port'];


        $this->_config ['w'] ['host'] = $config ['w'] ['host'];
        $this->_config ['w'] ['name'] = $config ['w'] ['name'];
        $this->_config ['w'] ['user'] = $config ['w'] ['user'];
        $this->_config ['w'] ['pass'] = $config ['w'] ['pass'];
        $this->_config ['w'] ['port'] = $config ['w'] ['port'];
    }

    /**
     * 杩炴帴鏁版嵁搴�	 *
     * @param String $type r|w
     * @return Object mysqli
     */
    public function connect($type = db::NODE_READ) {
        $instance = $type;
        $allType  = array(db::NODE_READ, db::NODE_WRITE);
        if (!in_array("" . $type, $allType)) {
            //throw new SysException(ErrNo::DB_NODE_TYPE_ERR);
        }
        if (empty($this->$instance)) {
            $conn = new mysqli($this->_config [$type] ['host'], $this->_config [$type] ['user'],
                               $this->_config [$type] ['pass'], $this->_config [$type] ['name'],
                               $this->_config [$type] ['port']
            );

            if ($conn->connect_errno) {
                unset($this->_config[$type]['pass']);
//                $this->writeErr(ErrNo::DB_CONNECT_ERR,
//                                sprintf('Connect Server Failed:"%s" Errmsg:"%s" config:"%s"',
//                                        $conn->connect_errno, $conn->connect_error,
//                                        json_encode($this->_config[$type])));

                //throw new SysException(ErrNo::DB_CONNECT_ERR,
                  //                     'db error');
            }

            if (isset($this->_config['charset'])) {
                $this->_character_set = isset($this->_config['charset']) ? $this->_config['charset'] : $this->_character_set;
            }
            $conn->set_charset($this->_character_set);
        }

        return $this->$instance = $conn;
    }

    /**
     * 查询类sql，查询为空返回null
     *
     * @param String $sql
     * @param $type r|w|null 默认为根据sql语句判断，需要强制指定读取主库时指定为w
     * @return array | null 如果存在数据，返回类似array(0=>..,..),无数据时返回null
     */
    public function select($sql, $type = NULL) {
        if (is_null($type)) {
            $type = ('select' == strtolower(substr(trim($sql), 0, 6))) ? 'r' : 'w';
        }
        $instance = $type;
        if (!$this->$instance) {
            $this->connect($type);
        }

        if (($result = $this->$instance->query($sql)) === FALSE) {
//            $this->writeErr(ErrNo::DB_QUERY_ERR,
//                            sprintf('mysql db errno:"%s" Errmsg:"%s" sql:"%s"',
//                                    $this->$instance->errno, $this->$instance->error, $sql),
//                                    ErrNo::DB_QUERY_ERR);
//            throw new SysException(ErrNo::DB_QUERY_ERR,'db error');
        }

        $rows = null;

        while ($result && $row = $result->fetch_assoc()) {
            $rows [] = $row;
        }

        $result->free();
        return $rows;
    }

    /**
     * 鎵цsql
     *
     * @param String $sql
     */
    public function write($sql) {
        $instance = db::NODE_WRITE;
        if (!$this->$instance) {
            $this->connect($instance);
        }

        $result = $this->$instance->query($sql);

        if (true === $result) {
            return true;
        } else {
//            $this->writeErr(ErrNo::DB_WRITE_ERR,
//                            sprintf('mysql db errno:"%s" Errmsg:"%s" sql:"%s"',
//                                    $this->$instance->errno, $this->$instance->error, $sql),
//                                    ErrNo::DB_WRITE_ERR);
//            throw new SysException(ErrNo::DB_WRITE_ERR,'db error');
        }
    }

    public function insert_id() {
        return $this->w->insert_id;
    }

    public function autocommit($flag = FALSE, $type = db::NODE_WRITE) {
        $instance = $type;
        if (!$this->$instance) {
            $this->connect($type);
        }
        return $this->$instance->autocommit($flag);
    }

    public function commit($type = db::NODE_WRITE) {
        $instance = $type;
        if (!$this->$instance) {
            $this->connect($type);
        }
        if ($this->$instance->commit() === FALSE) {
//            $this->writeErr(ErrNo::DB_COMMIT_ERR,
//                            sprintf('mysql db errno:"%s" Errmsg:"%s" node:"%s"',
//                                    $this->$instance->errno, $this->$instance->error, $type));
//            throw new SysException(ErrNo::DB_COMMIT_ERR,'db error');
        }
    }

    public function rollback($type = db::NODE_WRITE) {
        $instance = $type;
        if (!$this->$instance) {
            $this->connect($type);
        }
        if (TRUE === $this->$instance->rollback()) {
            return TRUE;
        } else {
            $this->$instance->autocommit(FALSE);
//            $this->writeErr(ErrNo::DB_ROLLBACK_ERR,
//                            sprintf('mysql db errno:"%s" Errmsg:"%s" node:"%s"',
//                                    $this->$instance->errno, $this->$instance->error, $type));
//            throw new SysException(ErrNo::DB_ROLLBACK_ERR, 'db error');
        }
    }

    public function real_escape_string($v) {
        if (!$this->r) {
            $this->connect(db::NODE_READ);
        }
        return $this->r->real_escape_string($v);
    }

    /**
     * 閿�瘉鏌愪釜鏁版嵁搴撹繛鎺ュ疄渚�     * @param String $type r|w
     *
     */
    public function close($type = 'r') {
        $conn = $type;
        unset($this->$conn);
        //娓呴櫎娉ㄥ唽琛ㄥ唴瀵硅薄瀹炰緥
        global $registry;
        if (!isset($registry)) {
            $registry = cls_base_registry::get_instance();
        }
        $registry->clear($this->_config [$type] ['dsn']);
    }

//    private function writeErr($errNo, $errMsg) {
//        Log::add('ERRNO:' . $errNo . ' ERRMSG:' . $errMsg, 'DBERR', Log::FATAL);
//    }

}
