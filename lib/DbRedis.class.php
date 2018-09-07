<?php

/**
  +-----------------------------------------------------------------------------
 * 数据模型层基类-Redis
  +-----------------------------------------------------------------------------
 * 支持 Master/Slave 的负载集群
 * @author 崔俊涛
 * @date 2016-04
 */
class DbRedis {

    //是否使用 M/S 的读写集群方案
    private $_isUseCluster = false;
    //Slave 句柄标记
    private $_sn = 0;
    //服务器连接句柄
    private $_linkHandle = array(
        //只支持一台 Master
        'master' => null,
        //可以有多台 Slave
        'slave' => array()
    );

    public function __construct($params = array('host' => '127.0.0.1', 'port' => 6379)) {
        $this->connect($params);
    }

    /**
     * 连接服务器,注意：这里使用长连接，提高效率，但不会自动关闭
     *
     * @param array $config Redis服务器配置
     * @param boolean $isMaster 当前添加的服务器是否为 Master 服务器
     * @return boolean
     */
    public function connect($config, $isMaster = true) {
        //default port
        if (!isset($config['port'])) {
            $config['port'] = 6379;
        }
        //设置 Master 连接
        if ($isMaster) {
            $this->_linkHandle['master'] = new Redis();
            $ret = $this->_linkHandle['master']->pconnect($config['host'], $config['port']);
            if (isset($config['password'])) {
                $this->_linkHandle['master']->auth($config['password']);
            }
        } else {
            //多个 Slave 连接
            $this->_linkHandle['slave'][$this->_sn] = new Redis();
            $ret = $this->_linkHandle['slave'][$this->_sn]->pconnect($config['host'], $config['port']);
            if (isset($config['password'])) {
                $this->_linkHandle['slave'][$this->_sn]->auth($config['password']);
            }
            ++$this->_sn;
        }
        return $ret;
    }

    /**
     * 关闭连接
     *
     * @param int $flag 关闭选择 0:关闭 Master 1:关闭 Slave 2:关闭所有
     * @return boolean
     */
    public function close($flag = 2) {
        switch ($flag) {
            //关闭 Master
            case 0:
                $this->getRedis()->close();
                break;
            //关闭 Slave
            case 1:
                for ($i = 0; $i < $this->_sn; ++$i) {
                    $this->_linkHandle['slave'][$i]->close();
                }
                break;
            //关闭所有
            case 1:
                $this->getRedis()->close();
                for ($i = 0; $i < $this->_sn; ++$i) {
                    $this->_linkHandle['slave'][$i]->close();
                }
                break;
        }
        return true;
    }

    /**
     * 得到 Redis 原始对象可以有更多的操作
     *
     * @param boolean $isMaster 返回服务器的类型 true:返回Master false:返回Slave
     * @param boolean $slaveOne 返回的Slave选择 true:负载均衡随机返回一个Slave选择 false:返回所有的Slave选择
     * @return redis object
     */
    public function getRedis($isMaster = true, $slaveOne = true) {
        //只返回 Master
        if ($isMaster) {
            return $this->_linkHandle['master'];
        } else {
            return $slaveOne ? $this->_getSlaveRedis() : $this->_linkHandle['slave'];
        }
    }

    /**
     * 判断一个key是否存在
     * @param type $key
     * @return type
     */
    public function exists($key) {
        return $this->getRedis()->exists($key);
    }

    public function keys($key) {
        return $this->getRedis()->keys($key);
    }

    /**
     * 写缓存
     *
     * @param string $key 组存KEY
     * @param string $value 缓存值
     * @param int $expire 过期时间， 0:表示无过期时间
     */
    public function set($key, $value, $expire = 0) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        //永不超时
        if ($expire == 0) {
            $ret = $this->getRedis()->set($key, $value);
        } else {
            $ret = $this->getRedis()->setex($key, $expire, $value);
        }
        return $ret;
    }

    /**
     * 读缓存
     *
     * @param string $key 缓存KEY,支持一次取多个 $key = array('key1','key2')
     * @return string || boolean  失败返回 false, 成功返回字符串
     */
    public function get($key, $decode = FALSE) {
        //是否一次取多个值
        $func = is_array($key) ? 'mGet' : 'get';
        //没有使用M/S
        if (!$this->_isUseCluster) {
            $rs = $this->getRedis()->{$func}($key);
        } else { //使用了 M/S
            $rs = $this->_getSlaveRedis()->{$func}($key);
        }
        if ($decode === TRUE) {
            $rs = $this->dejson($rs);
        }
        return $rs;
    }

    function dejson($string) {
        if (empty($string) OR ! $string) return FALSE;
        $string = str_replace(PHP_EOL, "", $string);
        $string = str_replace('&quot;', "\"", $string);
        $data = json_decode($string, TRUE);
        return (json_last_error() == JSON_ERROR_NONE) ? $data : FALSE;
    }

    /*
      //magic function
      public function __call($name,$arguments){
      return call_user_func($name,$arguments);
      }
     */

    /**
     * 条件形式设置缓存，如果 key 不存时就设置，存在时设置失败
     *
     * @param string $key 缓存KEY
     * @param string $value 缓存值
     * @return boolean
     */
    public function setnx($key, $value) {
        return $this->getRedis()->setnx($key, $value);
    }

    /**
     * 删除缓存
     *
     * @param string || array $key 缓存KEY，支持单个健:"key1" 或多个健:array('key1','key2')
     * @return int 删除的健的数量
     */
    public function remove($key) {
        //$key => "key1" || array('key1','key2')
        return $this->getRedis()->delete($key);
    }

    /**
     * 值加加操作,类似 ++$i ,如果 key 不存在时自动设置为 0 后进行加加操作
     *
     * @param string $key 缓存KEY
     * @param int $default 操作时的默认值
     * @return int　操作后的值
     */
    public function incr($key, $default = 1) {
        if ($default == 1) {
            return $this->getRedis()->incr($key);
        } else {
            return $this->getRedis()->incrBy($key, $default);
        }
    }

    /**
     * 值减减操作,类似 --$i ,如果 key 不存在时自动设置为 0 后进行减减操作
     *
     * @param string $key 缓存KEY
     * @param int $default 操作时的默认值
     * @return int　操作后的值
     */
    public function decr($key, $default = 1) {
        if ($default == 1) {
            return $this->getRedis()->decr($key);
        } else {
            return $this->getRedis()->decrBy($key, $default);
        }
    }

    /**
     * 添空当前数据库
     *
     * @return boolean
     */
    public function clear() {
        return $this->getRedis()->flushDB();
    }

    /* =================== 以下私有方法 =================== */

    /**
     * 随机 HASH 得到 Redis Slave 服务器句柄
     *
     * @return redis object
     */
    private function _getSlaveRedis() {
        //就一台 Slave 机直接返回
        if ($this->_sn <= 1) {
            return $this->_linkHandle['slave'][0];
        }
        //随机 Hash 得到 Slave 的句柄
        $hash = $this->_hashId(mt_rand(), $this->_sn);
        return $this->_linkHandle['slave'][$hash];
    }

    /**
     * 根据ID得到 hash 后 0～m-1 之间的值
     *
     * @param string $id
     * @param int $m
     * @return int
     */
    private function _hashId($id, $m = 10) {
        //把字符串K转换为 0～m-1 之间的一个值作为对应记录的散列地址
        $k = md5($id);
        $l = strlen($k);
        $b = bin2hex($k);
        $h = 0;
        for ($i = 0; $i < $l; $i++) {
            //相加模式HASH
            $h += substr($b, $i * 2, 2);
        }
        $hash = ($h * 1) % $m;
        return $hash;
    }

    /**
     * 依次从左侧
     */
    public function lpush($key, $value) {
        return $this->getRedis()->lpush($key, $value);
    }

    /**
     * 依次从右侧添加
     * @param type $key
     * @param type $value
     * @return type
     */
    public function rpush($key, $value) {
        return $this->getRedis()->rpush($key, $value);
    }

    /**
     * 从左侧取一条，并删除数据
     */
    public function lpop($key) {
        return $this->getRedis()->lpop($key);
    }

    /**
     * 从右侧取一条并删除数据
     */
    public function rpop($key) {
        return $this->getRedis()->rpop($key);
    }

    /**
     * 获取队列指定范围的数据
     * 其中 0 表示列表的第一个元素， 1 表示列表的第二个元素，以此类推
     * 你也可以使用负数下标，以 -1 表示列表的最后一个元素， -2 表示列表的倒数第二个元素，以此类推。
     * lrange($key, 0, 10)
     * lrange($key, 0, -1)  取所有
     * lrange($key, -1, -1)  取最后一个
     * lrange($key, -2, -1)  取最后两个
     */
    public function lrange($key, $start, $end) {
        return $this->getRedis()->lrange($key, $start, $end);
    }

    /**
     * 获取队列长度
     * @param type $key
     * @return type
     */
    public function llen($key) {
        return $this->getRedis()->llen($key);
    }

    /**
     * 保留指定的list
     * Redis Ltrim 对一个列表进行修剪(trim)，就是说，让列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除。
     * 下标 0 表示列表的第一个元素，以 1 表示列表的第二个元素，以此类推。 
     * 你也可以使用负数下标，以 -1 表示列表的最后一个元素， -2 表示列表的倒数第二个元素，以此类推。
     */
    public function ltrim($key, $start, $end) {
        return $this->getRedis()->ltrim($key, $start, $end);
    }

    /**
     *    set hash opeation
     */
    public function hset($name, $key, $value) {
        if (is_array($value)) {
            return $this->getRedis()->hset($name, $key, serialize($value));
        }
        return $this->getRedis()->hset($name, $key, $value);
    }

    /**
     *    get hash opeation
     */
    public function hget($name, $key = null, $serialize = true) {
        if ($key) {
            $row = $this->getRedis()->hget($name, $key);
            if ($row && $serialize) {
                $row = unserialize($row);
                return $row;
            } else {
                return array();
            }
        }
//        return $this->getRedis()->hgetAll($name);
    }

    /**
     *    delete hash opeation
     */
    public function hdel($name, $key = null) {
        if ($key) {
            return $this->getRedis()->hdel($name, $key);
        }
        return $this->getRedis()->hdel($name);
    }

    /**
     * Transaction start
     */
    public function multi() {
        return $this->getRedis()->multi();
    }

    /**
     * Transaction send
     */
    public function exec() {
        return $this->getRedis()->exec();
    }

    /*     * ******************集合操作******************** */

    /**
     * 
     * @param type $key  集合标示
     * @param type $value  集合成员，可添加多个，添加多个用空格隔开
     * @return type
     */
    public function sadd($key, $value) {
        if (is_array($value)) {
            foreach ($value as $val) {
                $this->getRedis()->sadd($key, $val);
            }
            return count($value);
        } else {
            return $this->getRedis()->sadd($key, $value);
        }
    }

    /**
     * 返回集合所有成员数
     * @param type $key
     * @return type
     */
    public function smembers($key) {
        return $this->getRedis()->smembers($key);
    }

    /**
     * 随机获取集合中的值
     * @param type $key
     * @param type $rand_num 随机数
     * @return type
     */
    public function srandmember($key, $rand_num) {
        return $this->getRedis()->srandmember($key, $rand_num);
    }

    /**
     * 删除集合中一个或者多个值
     * @param type $key
     * @param type $value
     * @return type
     */
    public function srem($key, $value) {
        return $this->getRedis()->srem($key, $value);
    }

    /**
     * 获取成员数  
     * @param type $key
     * @return type
     */
    public function scard($key) {
        return $this->getRedis()->scard($key);
    }

    /**
     * 判断一个元素是否在某个集合中存在
     * @param type $key
     * @param type $value
     * @return type
     */
    public function sismember($key, $value) {
        return $this->getRedis()->sismember($key, $value);
    }

    /**
     * 命令用于移除有序集中，指定排名(rank)区间内的所有成员。
     * @param type $key
     */
    public function zRemRangeByRank($key, $start, $stop) {
        return $this->getRedis()->zRemRangeByRank($key, $start, $stop);
    }

    /**
     * 命令用于移除有序集中，指定分数（score）区间内的所有成员
     * @param type $key
     * @param type $min
     * @param type $max
     * @return type
     */
    public function zRemRangeByScore($key, $min, $max) {
        return $this->getRedis()->zRemRangeByScore($key, $min, $max);
    }

    /**
     * 命令用于移除有序集中的一个或多个成员，不存在的成员将被忽略。
     */
    public function zRem($key, $member) {
        return $this->getRedis()->zRem($key, $member);
    }

    /**
     * 递增排序
     */
    public function zRange($key, $start, $stop) {
        return $this->getRedis()->zRange($key, $start, $stop);
    }

    /**
     * 命令返回有序集中，指定区间内的成员。递减排序
     */
    public function zrevrange($key, $start, $stop, $flag = false) {
        return $this->getRedis()->zrevrange($key, $start, $stop, $flag);
    }

    /**
     * 某个成员累加1
     * @param type $key
     * @param type $member
     * @param type $incr
     * @return type
     */
    public function zIncrBy($key, $member, $incr = 1) {
        return $this->getRedis()->zIncrBy($key, $incr, $member);
    }

    /**
     * 返回有序集合成员的个数
     * @param type $key
     * @return type
     */
    public function zCard($key) {
        return $this->getRedis()->zCard($key);
    }

    /**
     * 返回成员的分数值
     * @param type $key
     * @return type
     */
    public function zScore($key, $member) {
        return $this->getRedis()->zScore($key, $member);
    }

}

/*
//End Class
//================= TEST DEMO =================
//只有一台 Redis 的应用
$redis = new DbYspRedis();
$redis->connect(array('host' => '127.0.0.1', 'port' => 6379));

$cron_id = 10001;
$CRON_KEY = 'CRON_LIST';
$PHONE_KEY = 'PHONE_LIST:' . $cron_id;
//cron info
$cron = $redis->hget($CRON_KEY, $cron_id);
if (empty($cron)) {

    $cron = array('id' => 10, 'name' => 'jackluo'); //mysql data
    $redis->hset($CRON_KEY, $cron_id, $cron); //set redis    
}
//phone list
$phone_list = $redis->lrange($PHONE_KEY, 0, -1);
print_r($phone_list);

if (empty($phone_list)) {
    $phone_list = explode(',', '13228191831,18608041585');
    //mysql data
    //join  list
    if ($phone_list) {
        $redis->multi();
        foreach ($phone_list as $phone) {
            $redis->lpush($PHONE_KEY, $phone);
        }
        $redis->exec();
    }
}

print_r($phone_list);

$list = $redis->hget($cron_list,);
var_dump($list);


$redis->set('id',35);
$redis->lpush('test','1111');
$redis->lpush('test','2222');
$redis->lpush('test','3333');

$list = $redis->lrange('test',0,-1);
print_r($list);
$lpop = $redis->lpop('test');
print_r($lpop);
$lpop = $redis->lpop('test');
print_r($lpop);
$lpop = $redis->lpop('test');
print_r($lpop);

//var_dump($redis->get('id'));
*/