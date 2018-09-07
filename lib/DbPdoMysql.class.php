<?php

/**
  +-----------------------------------------------------------------------------
 * 数据模型层基类-MySQL数据库-PDO
  +-----------------------------------------------------------------------------
 * @author 崔俊涛
 * @date 2017-11
 */
class DbPdoMysql {

    //连接数据对象
    private $_pdo = null;
    //是否开启事务
    public $isFlag = false;
    //数据库连接配置
    private $db_config;
    //当前页面执行的sql语句
    public $sqls = array();
    //执行SQL的句柄
    private $_query_id = null;

    /**
     * 构造函数
     * @param array $db_config 数据库连接的配制变量
     */
    function __construct($db_config) {
        $this->db_config = $db_config;
    }

    /**
     * 数据库连接
     */
    private function conn() {
        if (null === $this->_pdo) {
            try {
                $this->_pdo = NULL;
                $this->_pdo = new PDO('mysql:host=' . $this->db_config['host'] . ';port=' . $this->db_config['port'] . ';dbname=' . $this->db_config['dbname'], $this->db_config['user'], $this->db_config['pwd'], array(PDO::ATTR_PERSISTENT => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                $this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $this->_pdo->exec("SET NAMES 'utf8'");
            } catch (PDOException $e) {
                $this->halt($e);
            }
        }
    }

    /**
     * Ping mysql server
     *
     * @param boolean $reconnect
     * @return boolean
     */
    function ping($reconnect = true) {
        if ($this->_pdo && $this->_pdo->query('select 1')) {
            return true;
        }

        if ($reconnect) {
            $this->_pdo = NULL;
            $this->conn();
            return $this->ping(false);
        }

        return false;
    }

    /**
     * 析构函数
     * 关闭数库连接
     */
    function __destruct() {
        $this->_pdo = null;
    }

    /**
     * 输出错误信息
     * @param string $sql 当前的sql语句
     */
    private function halt(PDOException $e, $sql = '') {
        if (defined('DB_DEBUG') && DB_DEBUG == true) {
            $html = '<style type="text/css">' . "\n";
            $html .= '.msyql_error_info{clear:both;font-size:13px;color:#000;margin:5px;border:1px solid red;padding:5px;background-color:#FFFFCC;}' . "\n";
            $html .= '</style>' . "\n";
            $html .= '<div class="msyql_error_info">' . "\n";
            $html .= '<strong>错误提示：</strong>' . $e->getMessage() . "<br />\n";
            $html .= '<strong>错误编号：</strong>' . $e->getCode() . "<br />\n";
            $html .= '<strong>错误信息：</strong>' . $e->getFile() . '[' . $e->getLine() . ']' . "<br />\n";
            $html .= '<strong>SQL语句:</strong>' . $sql . "<br />\n";
            $html .= '<strong>当前页面：</strong>http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"] . "\n";
            $html .= '</div>' . "\n";
            echo $html;
            exit;
        } else {
            return false;
        }
    }

    /**
     * 执行SQL语句
     * @param string $sql SQL语句
     */
    function querySql($sql) {
        $this->ping();
        if ($this->conn() === FALSE) {
            return FALSE;
        }
        $this->sqls[] = $sql;
        if ($this->_query_id) {
            $this->_query_id = null;
        }
        try {
            return $this->_query_id = $this->_pdo->query($sql);
        } catch (PDOException $e) {
            $this->halt($e, $sql);
        }
    }

    /**
     * 打印所有的SQL语句
     */
    function printSqls() {
        foreach ($this->sqls as $key => $sql) {
            echo '[' . $key . '] -- ' . $sql . "<br>\n";
        }
    }

    /**
     * 获取构造好的INSERT语句
     * @param string $table_name 表名
     * @param string $field_array 字段一维数组
     * @return string 返回标准安全的insert语句
     */
    function getInsertSql($table_name, $field_array) {
        $field_str = '';
        $value_str = '';
        foreach ($field_array as $key => $value) {
            $field_str .= "`$key`,";
            $value_str .= "'$value',";
        }
        $field_str = rtrim($field_str, ',');
        $value_str = rtrim($value_str, ',');
        return "INSERT INTO $table_name($field_str) VALUES($value_str)";
    }

    /**
     * 执行INSERT SQL语句
     * @param string $table_name 表名
     * @param array $field_array 字段一维数组
     * @return integer 整数

     */
    function sqlInsert($table_name, $field_array) {
        $sql = $this->getInsertSql($table_name, $field_array);
        $this->querySql($sql);
        if ($this->_query_id) {
            return $this->_pdo->lastInsertId();
        } else {
            return 0;
        }
    }

    /**
     * 获取构造好的Update语句
     * @param string $table_name 表名
     * @param string $field_array 字段一维数组
     * @param string $where 条件
     * @param array $num_no_strong 哪些值不需要加引号
     * @return string 返回构造好的SQL语句
     */
    function getUpdateSql($table_name, $field_array, $where, $num_no_strong = array(0)) {
        $field_str = '';
        $i = 0;
        foreach ($field_array as $key => $value) {
            $i++;
            if (in_array($i, $num_no_strong)) {
                $field_str .= "`$key`={$value},";
            } else {
                $field_str .= "`$key`='{$value}',";
            }
        }
        $field_str = rtrim($field_str, ',');
        return trim("UPDATE {$table_name} SET {$field_str} {$where}");
    }

    /**
     * 执行SELECT语句
     * @param string $table_name 表名
     * @param array $field_str 字段列表，用逗号分隔
     * @param string $where 查询条件
     * @param string $limit 限制行数
     * @param string $ordery_by 排序方式
     * @param string $group_by 分组方式
     * @return array 返回为数组的结果
     */
    function getMoreSelect($table_name, $field_str = '*', $where = '', $ordery_by = '', $limit = '', $group_by = '', $having = '') {
        $sql = trim("SELECT $field_str FROM $table_name $where $group_by $having $ordery_by $limit");
        $this->querySql($sql);
        if ($this->_query_id) {
            $this->_query_id->setFetchMode(PDO::FETCH_ASSOC);
            return $this->_query_id->fetchAll();
        } else {
            return array();
        }
    }

    /**
     * 只取一行记录
     * @param string $table_name 表名
     * @param string $field_str 字段列表，中间用逗号分隔
     * @param string $where WHERE条件，写的时候带WHERE
     * @param string $limit LIMIT限制条件
     * @param string $ordery_by 排序
     * @param string $group_by 分组
     * @param string $having having统计条件
     * @return resources|boolean 返回数组或者布尔值
     */
    function getOneSelect($table_name, $field_str = '*', $where = '', $ordery_by = '') {
        $sql = "SELECT $field_str FROM $table_name $where $ordery_by LIMIT 1";
        $this->querySql($sql);
        if ($this->_query_id) {
            $this->_query_id->setFetchMode(PDO::FETCH_ASSOC);
            $info = $this->_query_id->fetch();
            if ($info === false) {
                return array();
            } else {
                return $info;
            }
        } else {
            return array();
        }
    }

    /**
     * 直接运行SQL并返回结果
     * @param string $sql 主要用于手写原生SQL
     * @return array 二维数组或空数组
     */
    function runSql($sql) {
        $this->querySql($sql);
        if ($this->_query_id) {
            $this->_query_id->setFetchMode(PDO::FETCH_ASSOC);
            return $this->_query_id->fetchAll();
        } else {
            return array();
        }
    }

    /**
     * 执行UPDATE SQL语句
     * @param string $table_name 表名
     * @param array $field_array 字段一维数组
     * @param string $where WHERE条件
     * @param array $num_no_strong 哪些值不需要加引号
     * @return boolean 返回影响行数或否定值
     */
    function sqlUpdate($table_name, $field_array, $where, $num_no_strong = array(0)) {
        $field_str = '';
        $i = 0;
        foreach ($field_array as $key => $value) {
            $i++;
            if (in_array($i, $num_no_strong)) {
                $field_str .= "`$key`={$value},";
            } else {
                $field_str .= "`$key`='{$value}',";
            }
        }
        $field_str = rtrim($field_str, ',');
        $sql = trim("UPDATE {$table_name} SET {$field_str} {$where}");
        $this->querySql($sql);
        if ($this->_query_id) {
            return $this->_query_id->rowCount();
        } else {
            return false;
        }
    }

    /**
     * 删除操作
     * @param string $table_name 表名
     * @param string $where 条件
     * @param string $limit 限制行数
     * @param string $order_by 排序方法
     * @param string $group_by  分组方式
     * @param string $having 条件
     * @return boolean 返回影响行数或否定值
     */
    function sqlDelete($table_name, $where, $limit = '', $order_by = '') {
        $sql = trim("DELETE FROM $table_name $where $order_by $limit");
        $this->querySql($sql);
        if ($this->_query_id) {
            return $this->_query_id->rowCount();
        } else {
            return false;
        }
    }

    /**
     * 取当前数据库的所有数据表信息
     * @param string $database 所属库名
     * @param array $table_arr 要取哪些表，数组的第一个元素是别名，第二个是真实的表名
     * @return array
     */
    function getTableList($database, $table_arr = array()) {
        $table_list_new = array();
        foreach ($table_arr as $table_names) {
            $table_name_arr = explode('|', $table_names);
            $replace_name = $table_name_arr[0];
            $table_name = isset($table_name_arr[1]) ? $table_name_arr[1] : $table_name_arr[0];
            $sql = "SELECT TABLE_NAME,ENGINE,CREATE_TIME,UPDATE_TIME,TABLE_COLLATION,TABLE_COMMENT FROM INFORMATION_SCHEMA.TABLES WHERE table_name='$table_name' AND table_schema='$database'";
            $this->querySql($sql);
            $table_list_new[$replace_name] = $table[0];
        }
        return $table_list_new;
    }

    /**
     * 取当前表中的所有字段及字段属性和注释
     * @param string $database 数据库名
     * @param stirng $table_name 数据表名
     * @return array
     */
    function getTableColumInfo($database, $table_name) {
        $sql = "SELECT COLUMN_NAME,COLUMN_DEFAULT,IS_NULLABLE,COLUMN_TYPE,EXTRA,COLUMN_COMMENT,CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name='{$table_name}' AND table_schema='{$database}'";
        $this->querySql($sql);
        $this->_query_id->setFetchMode(PDO::FETCH_ASSOC);
        return $this->_query_id->fetchAll();
    }

    /**
     * 取当前数据库版本号
     */
    function getMysqlVersion() {
        $sql = "select version() as v";
        $this->querySql($sql);
        $this->_query_id->setFetchMode(PDO::FETCH_ASSOC);
        $rs = $this->_query_id->fetch();
        return $rs['v'];
    }

    /**
     * 自动判断的插入或更新一条记录
     * insert的字段中必须包含唯一性索引的字段
     * @param string $table_name 表名
     * @param array $array_insert 要插入的字段和值数据
     * @param string $field_update
     * @return int|boolean 返回影响行数或否定值
     */
    function sqlReplaceInsert($table_name, $array_insert, $field_update) {
        $sql = $this->getInsertSql($table_name, $array_insert);
        $sql .= " ON DUPLICATE KEY UPDATE $field_update";
        $this->querySql($sql);
        if ($this->_query_id) {
            return $this->_query_id->rowCount();
        } else {
            return false;
        }
    }

    /**
     * 开启事务
     * @return resource
     */
    function transactionBegin() {
        $this->ping();
        //这个是通过设置属性方法进行关闭自动提交
        $this->_pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
        //开启异常处理，本类new的时候已开启
        //$this->_pdo->setAttribute(PDO::ATTR_ERRMODE,  PDO::ERRMODE_EXCEPTION);
        //开启事务处理
        $this->_pdo->beginTransaction();
    }

    /**
     * 执行原生的SQL语句
     * @param string $sql sql语句
     */
    function execSql($sql) {
        return $this->_pdo->exec($sql);
    }

    /**
     * 回滚 
     * @return resource
     */
    function transactionRollback() {
        $this->_pdo->rollback();
        $this->transactionEnd();
    }

    /**
     * 提交执行
     * @return resource
     */
    function transactionCommit() {
        $this->_pdo->commit();
        $this->transactionEnd();
    }

    /**
     * 自动提交，如果最后不自动提交，整个SQL运行是完不成的
     */
    private function transactionEnd() {
        $this->_pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
    }

}
