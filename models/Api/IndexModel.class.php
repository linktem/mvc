<?php

/**
 * @author 林维
 * @date 2018-9-7
 */
class IndexModel extends DbConfig {

    //数据库连接句柄
    private $conn;
    private $table;

    function __construct() {
        $this->table = 'article';
        $this->conn = parent::dbConn();
    }

    function getList($where) {
        return $this->conn->getMoreSelect($this->table, '*', $where);
    }

}
