<?php

/**
  +-----------------------------------------------------------------------------
 * 数据模型层基类-MongoDB
  +-----------------------------------------------------------------------------
 * @author 崔俊涛
 * @date 2018-05
 */
class DbMongoDB {

    //数据库对象
    private $mongo = null;

    /**
     * 构造函数
     * @param array $mongo_config 数据库连接的配制变量
     */
    function __construct($mongo_config) {
        try {
            if (empty($mongo_config['username'])) {
                $connection = new MongoClient('mongodb://' . $mongo_config['host'] . ':' . $mongo_config['port'], $mongo_config['options']);
            } else {
                $connection = new MongoClient('mongodb://' . $mongo_config['username'] . ':' . $mongo_config['password'] . '@' . $mongo_config['host'] . ':' . $mongo_config['port'], $mongo_config['options']);
            }
            $db_name = $mongo_config['dbname'];
            $this->mongo = $connection->selectDB($db_name);
        } catch (PDOException $e) {
            $this->halt($e);
        }
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
     * 执行插入语句
     * @param string $table_name 表名
     * @param array $data 字段键值对数组
     * @return integer 整数
     */
    function sqlInsert($table_name, $data) {
        try {
            //第二个参数是执行安全写入的方法
            $this->mongo->selectCollection($table_name)->insert($data, array('w' => true));
            return $data['_id'];
        } catch (MongoCursorException $e) {
            $this->error = $e->getMessage();
            return 0;
        }
    }

    /**
     * 查询表中所有数据
     * @param string $table_name 表名
     * @param array $where 条件
     * @param array $fields 要取的字段名
     * @param array $sort 排序
     * @param string $skip 跳过之前多少数据
     * @param string $limit 所取条数
     * @return array
     */
    public function getMoreSelect($table_name, $where = array(), $fields = array(), $sort = array(), $skip = '', $limit = '') {
        if (!empty($fields)) {
            $data = $this->mongo->selectCollection($table_name)->find($where, $fields);
        } else {
            $data = $this->mongo->selectCollection($table_name)->find($where);
        }

        if (!empty($sort)) {
            $data = $data->sort($sort);
        }

        if (!empty($skip)) {
            $data = $data->skip($skip);
        }

        if (!empty($limit)) {
            $data = $data->limit($limit);
        }
        $new_data = array();
        foreach ($data as $val) {
            if ($val) {
                $new_data[] = $val;
            }
        }
        return $new_data;
    }

    /**
     * 查询指定一条数据
     * @param string $table_name 表名
     * @param array $where
     * @param array $sort 排序
     * @return int
     */
    public function getOneSelect($table_name, $where = array(), $fields = array(), $sort = array()) {
        if (!empty($where)) {
            $data = $this->mongo->selectCollection($table_name)->find($where, $fields);
        } else {
            $data = $this->mongo->selectCollection($table_name)->find();
        }
        if (!empty($sort)) {
            $data = $data->sort($sort);
        }
        $data = $data->limit(1);
        $new_data = array();
        foreach ($data as $val) {
            if ($val) {
                $new_data = $val;
            }
        }
        return $new_data;
    }

    /**
     * 获取唯一数据
     * @param string $table_name 表名
     * @param string $key 键名,只能是一个
     * @param array $query
     * @return array 自然下标的一维数组
     */
    public function getDistinct($table_name, $key, $query = array()) {
        if (!empty($query)) {
            $where = array('distinct' => $table_name, 'key' => $key, 'query' => $query);
        } else {
            $where = array('distinct' => $table_name, 'key' => $key);
        }

        $data = $this->mongo->command($where);
        if (isset($data['values'])) {
            return $data['values'];
        } else {
            return array();
        }
    }

    /**
     * 统计个数
     * @param string $table_name 表名
     * @param array $where 条件
     * @return int 数量
     */
    public function getCount($table_name, $where = array()) {
        if (!empty($where)) {
            return $this->mongo->$table_name->find($where)->count();
        } else {
            return $this->mongo->$table_name->find()->count();
        }
    }

    /**
     * 直接执行mongo命令
     * @param string $sql 主要用于手写原生SQL
     * @return array 二维数组或空数组
     */
    function runSql($sql) {
        return $this->mongo->execute($sql);
    }

    /**
     * 执行UPDATE SQL语句
     * @param string $table_name 表名
     * @param array $data 字段一维数组
     * @param string $where WHERE条件
     * @return boolean 返回影响行数或否定值
     */
    function sqlUpdate($table_name, $data, $where, $num_no_strong = array(0)) {
        $update_data = array();
        $i = 0;
        foreach ($data as $key => $value) {
            $i++;
            if (in_array($i, $num_no_strong)) {
                $update_data['$inc'][$key] = $value;
            } else {
                $update_data['$set'][$key] = $value;
            }
        }
        return $this->mongo->$table_name->update($where, $update_data, array('multiple' => true));
    }

    /**
     * 删除操作
     * @param string $table_name 表名
     * @param string $where 条件
     * @return int|boolean 返回影响行数或否定值
     */
    function sqlDelete($table_name, $where) {
        return $this->mongo->$table_name->remove($where);
    }

}
