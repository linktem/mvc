<?php

/**
  +-----------------------------------------------------------------------------
 * 类包基类
  +-----------------------------------------------------------------------------
 * @author 崔俊涛
 * @date 2016-04
 */
class LibBase {

    /**
      +--------------------------------------------------------------------------
     * 获取远程网址返回的状态码
     * @param string $url 网址
      +--------------------------------------------------------------------------
     */
    static function getHttpCode($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // 200
        curl_close($ch);
        return $http_code;
    }

    /**
      +--------------------------------------------------------------------------
     * CURL模拟POST请求
      +--------------------------------------------------------------------------
     * @param string $url 当前请求的URL
     * @data array $data 要发送的POST数据
     */
    static function curlPost($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = http_build_query($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }

    /**
      +--------------------------------------------------------------------------
     * CURL模拟FILE请求
      +--------------------------------------------------------------------------
     * @param string $url 网址
     * @param array $data 要发送的单条FILES数据
     */
    static function curlFile($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //图片信息
        $data_new = $data;
        //重新处理图片相关的数据
        $data_new['file'] = curl_file_create($data['file']['tmp_name'], $data['file']['type']);
        $data_new['file_name'] = $data['file']['name'];
        $data_new['file_size'] = $data['file']['size'];
        $data_new['file_type'] = $data['file']['type'];
        @curl_setopt($ch, CURLOPT_POSTFIELDS, $data_new);
        curl_setopt($ch, CURLOPT_INFILESIZE, $data_new['file_size']);
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }

    /**
      +--------------------------------------------------------------------------
     * url编码中文字符
      +--------------------------------------------------------------------------
     * @param array $array 将所有的中文字符进行URL编码
     */
    static private function urlencodeCn(&$array) {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (!is_array($value)) {
                    $array[$key] = urlencode($value);
                } else {
                    self::urlencodeCn($array[$key]);
                }
            }
        } else {
            $array = urlencode($array);
        }
        return $array;
    }

    /**
      +--------------------------------------------------------------------------
     * 将br转换成回车换行符
      +--------------------------------------------------------------------------
     */
    static function br2nl($text) {
        $text = preg_replace('/<br\\s*?\/??>/i', chr(13), $text);
        return preg_replace('/ /i', ' ', $text);
    }

    /**
      +--------------------------------------------------------------------------
     * 返回由JSON编码后的字符串，中文进行过转换处理
     * @param array $array 要用JSON编码的数组，必须是一个二维数组
     * 先对中文进行编码，JSON之后再解码。使用的时候直接输出即可
      +--------------------------------------------------------------------------
     */
    static function jsonEnCode($array) {
        //return stripslashes(urldecode(json_encode(self::urlencodeCn($array))));
        return json_encode($array);
        //return json_encode($array, JSON_UNESCAPED_UNICODE);
    }

    /**
      +--------------------------------------------------------------------------
     * 返回由JSON解码后的数组
     * @param string $string 要用JSON解码的字符串
     * 先对字符串进行反转义
     * 如果传入的是一个字数组，就直接返回空
      +--------------------------------------------------------------------------
     */
    static function jsonDeCode($string) {
//        if (empty($string)) {
//            return array();
//        }
//        $array = json_decode($string, true);
//        if (json_last_error() == 3) {
//            $string = substr(str_replace('\"', '"', json_encode($string)), 1, -1);
//            $array = json_decode($string, true);
//        }
//        return $array;

        return json_decode($string, true);
    }

    /**
      +--------------------------------------------------------------------------
     * 过滤文章内容中的多余标签
      +--------------------------------------------------------------------------
     * @param string $tags 需要保留的标签
     * @return string
     */
    static function getArticleContent($content, $tags = '') {
        $tags .= '<b><br><p><div><strong><u><span><img><ul><li><ol>';
        $content = strip_tags($content, $tags);
        return $content;
    }

    /**
      +--------------------------------------------------------------------------
     * 根据当前时间获取问候语
      +--------------------------------------------------------------------------
     * @return string
     */
    static function getGreetings() {
        $hour = date('G', time());
        if ($hour >= 0 && $hour < 6) {
            $str = '凌晨好！';
        }
        if ($hour >= 6 && $hour < 12) {
            $str = '上午好！';
        }
        if ($hour >= 12 && $hour < 19) {
            $str = '下午好！';
        }
        if ($hour >= 19 && $hour <= 23) {
            $str = '晚上好！';
        }
        return $str;
    }

    /**
      +--------------------------------------------------------------------------
     * 返回由对象属性组成的关联数组
      +--------------------------------------------------------------------------
     */
    static function getArray($obj) {
        if (is_object($obj)) {
            $obj = get_object_vars($obj);
        }
        if (is_array($obj)) {
            foreach ($obj as $key => $value) {
                $obj[$key] = self::getArray($value);
            }
        }
        return $obj;
    }

    /**
     * 获取文件大小，并自动配到出合适的单位
     * @param int $file_size 文件大小，以字节为最小单位
     * @return int 文件大小
     */
    static function getFileSize($file_size = 0) {
        //如果小于1024，则用bit为单位
        if ($file_size < 1024) {
            return $file_size . 'Bit';
        }
        //如果小于1024*1024，则用KB为单位
        if ($file_size >= 1024 && $file_size < 1024 * 1024) {
            return number_format(($file_size / 1024), 2) . 'KB';
        }
        //如果小于1024*1024*1024，则用M为单位
        if ($file_size >= 1024 * 1024 && $file_size < 1024 * 1024 * 1024) {
            return number_format(($file_size / 1024 / 1024), 2) . 'M';
        }
        //如果小于1024*1024*1024*1024，则用G来表示
        if ($file_size >= 1024 * 1024 * 1024) {
            return number_format(($file_size / 1024 / 1024 / 1024), 2) . 'G';
        }
        //如果小于1024*1024*1024*1024*1024，则用T来表示
        if ($file_size >= 1024 * 1024 * 1024 * 1024) {
            return number_format(($file_size / 1024 / 1024 / 1024 / 1024), 2) . 'TB';
        }
    }

    /**
     * 制作随机密码
     * @return array 一维数组，第一个元素为原始密码（8个字符），第二个元素为md5加密后的密码
     */
    static function createRandPassword() {
        $str = 'abcdefghijklmnopqrstuvwxyz1234567890ZBCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str_arr = str_split($str);
        $password = '';
        for ($i = 0; $i < 8; $i++) {
            $j = rand(0, 61);
            $password .= $str_arr[$j];
        }
        $password_md5 = md5($password);
        return array($password, $password_md5);
    }

    /**
     * 建立多层目录
     *
     * @param string $file_path 真实路径
     * @param string $mode 文件夹权限
     * @param string $skip_layer 跳过的层次,从根目录开始
     * @return boolean
     */
    static function makeMoreDir($file_path, $mode = 0777, $skip_layer = 2) {
        if (empty($file_path)) {
            return false;
        }
        $folderArray = explode('/', str_replace('//', '/', $file_path));
        $folder = "";
        $layer_num = 0;
        foreach ($folderArray as $folderOne) {
            $layer_num++;
            if ($layer_num <= $skip_layer) {
                continue;
            }
            $folder .= '/' . $folderOne;
            if (!file_exists($folder)) {
                @mkdir($folder);
                @chmod($folder, $mode);
            }
        }
        return true;
    }

    /**
     * 过滤查询关键词
     * @param type $words
     */
    static function wordsFilter($words) {
        $words = str_replace('/', '', str_replace('（', '(', str_replace('）', ')', $words)));
        $array = array('。', '\'', '-', ',', '，', '“', '”', '’', '‘', '|', "｜", '\\', "#", '%', '"', "'", ' ', '、', '+', '*', '=', '&', ';', '!', '?');
        foreach ($array as $val) {
            $words = str_replace($val, ' ', $words);
        }
        $array_arr = explode(' ', $words);
        $new_array_arr = array();
        foreach ($array_arr as $ls) {
            if (!empty($ls)) {
                $new_array_arr[] = trim($ls);
            }
        }
        return $new_array_arr;
    }

    /**
     * 加密函数
     * @param string $str 需要加密的数据
     * @param string $key 唯一密钥
     */
    static function enCode($str, $key, $iv = '8biWJeh0BxXWLByO') {
        //echo base64_encode(openssl_random_pseudo_bytes(16));
        //$iv = '8biWJeh0BxXWLByO';
//        $encrypted = openssl_encrypt($str, 'aes-256-cbc', base64_decode($key), 0, base64_decode($iv));
//        return base64_encode($encrypted);
        //echo '加密: ' . base64_encode($encrypted) . "\n";
        $encrypted = openssl_encrypt($str, 'aes-256-cbc', $key, 0, $iv);
        return $encrypted;
    }

    /**
     * 解密函数
     * @param string $str 需要解密的数据
     * @param string $key 唯一密钥
     */
    static function deCode($str, $key, $iv = '8biWJeh0BxXWLByO') {
        //echo base64_encode(openssl_random_pseudo_bytes(16));
        //$iv = '8biWJeh0BxXWLByO';
//        $encrypted = base64_decode($str);
//        return openssl_decrypt($encrypted, 'aes-256-cbc', base64_decode($key), 0, base64_decode($iv));
        return openssl_decrypt($str, 'aes-256-cbc', $key, 0, $iv);
    }

    /**
     * 加密函数---已经废弃的方案,请不要再使用
     * @param string $str 需要加密的数据
     * @param string $key 唯一密钥
     * 加密和解密函数中的参数cipher、key和mode必须一一对应，否则数据不能被还原
     * cipher——加密算法、key——密钥、data(str)——需要加密的数据、mode——算法模式、 iv——初始化向量
     * mode--电子代码本、密文块链、密文反馈、8位输出反馈、N位输出反馈和特殊的流模式
     * mode缩写--ecb、cbc、cfb、ofb、nofb和stream
     * @return string 加密后的数据（十六进制）
     */
    static function setEnCode($str, $key) {
        //开启加密算法
        $config = mcrypt_module_open('twofish', '', 'ecb', '');
        //建立 IV，并检测 key 的长度
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($config), MCRYPT_RAND);
        $keysize = mcrypt_enc_get_key_size($config);
        //生成 key 截取下长度
        $keystr = substr(md5($key), 0, $keysize);
        //初始化加密程序
        mcrypt_generic_init($config, $keystr, $iv);
        //加密, $encrypted 保存的是已经加密后的数据
        if (empty($str)) {
            return '';
        }
        $encrypted = mcrypt_generic($config, $str);
        //检测解密句柄，并关闭模块
        mcrypt_module_close($config);
        //转化为16进制
        $hexdata = bin2hex($encrypted);
        //返回
        return $hexdata;
    }

    /**
     * 解密函数---已经废弃的方案,请不要再使用
     * @param string $str 需要解密的数据
     * @param string $key 唯一密钥
     * 加密和解密函数中的参数cipher、key和mode必须一一对应，否则数据不能被还原
     * cipher——加密算法、key——密钥、data(str)——需要加密的数据、mode——算法模式、 iv——初始化向量
     * mode--电子代码本、密文块链、密文反馈、8位输出反馈、N位输出反馈和特殊的流模式
     * mode缩写--ecb、cbc、cfb、ofb、nofb和stream
     * @return string 解密后的数据
     */
    static function getDeCode($str, $key) {
        //开启加密算法
        $config = mcrypt_module_open('twofish', '', 'ecb', '');
        //建立 IV，并检测 key 的长度
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($config), MCRYPT_RAND);
        $keysize = mcrypt_enc_get_key_size($config);
        //生成 key 截取下长度
        $keystr = substr(md5($key), 0, $keysize);
        //初始化加密模块，用以解密
        mcrypt_generic_init($config, $keystr, $iv);
        //把加密后的十六进制数据 转成二进制数据
        $encrypted = pack('H*', $str);
        //解密
        $decrypted = mdecrypt_generic($config, $encrypted);
        //检测解密句柄，并关闭模块
        mcrypt_generic_deinit($config);
        mcrypt_module_close($config);
        //返回原始数据
        return trim($decrypted);
    }

    /**
     * 获取客户端IP
     * @return string IP地址
     * HTTP_ALI_CDN_REAL_IP 解析到阿里cdn后得到的ip
     */
    static function clientIp() {
        $keys = array('HTTP_ALI_CDN_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            if (empty($_SERVER[$key])) {
                continue;
            }
            $ips = explode(',', $_SERVER[$key], 1);
            $ip = $ips[0];
            if (false != ip2long($ip) && long2ip(ip2long($ip) === $ip)) {
                return $ips[0];
            }
        }

        return '0.0.0.0';
    }

    /**
     * 根据起始日期和月数计算间隔的天数
     * @param string $start_date 格式化后的日期，用-做为日期的分隔符
     * @param int $space_month 间隔的月数
     * @return array 返回间隔的天数和结束日期
     */
    static function getSpaceDays($start_date, $space_month) {
        $date_arr = explode('-', $start_date);
        $year = $date_arr[0];
        //判断月份的合理化值
        $month = $date_arr[1] + $space_month;
        if ($month > 12) {
            $year++;
            $month = $month - 12;
        }
        $end_date = $year . '-' . $month . '-' . $date_arr[2];
        //结束日期的秒数
        $end_date_int = strtotime($end_date);
        //起始日期的秒数
        $start_date_int = strtotime($start_date);

        $array['space_days'] = ceil(($end_date_int - $start_date_int) / (3600 * 24));
        $array['end_date'] = date('Y-m-d', $end_date_int);
        return $array;
    }

    /**
     * 截取字符
     */
    static function cut_str($string, $sublen, $fill_str = '', $start = 0, $code = 'UTF-8') {
        if ($code == 'UTF-8') {
            $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
            preg_match_all($pa, $string, $t_string);
            //$_str = '';
            $s_len = count($t_string[0]) - $start;
            //for($i=0;$i<$s_len;$i++)
            //{
            //    $_str .= '*';
            //}
            if ($s_len > $sublen) {
                return join('', array_slice($t_string[0], $start, $sublen)) . $fill_str;
            }
            return join('', array_slice($t_string[0], $start, $sublen));
        } else {
            $start = $start * 2;
            $sublen = $sublen * 2;
            $strlen = strlen($string);
            $tmpstr = '';
            for ($i = 0; $i < $strlen; $i++) {
                if ($i >= $start && $i < ($start + $sublen)) {
                    if (ord(substr($string, $i, 1)) > 129) {
                        $tmpstr .= substr($string, $i, 2);
                    } else {
                        $tmpstr .= substr($string, $i, 1);
                    }
                }
                if (ord(substr($string, $i, 1)) > 129) $i++;
            }
            if (strlen($tmpstr) < $strlen) $tmpstr .= "";
            return $tmpstr;
        }
    }

    /**
     * 记录日志
     */
    static function dlog($log_content = '', $path = '') {

        //默认每行日志必写的内容，统一在这里处理
        $log_file = '/tmp/abnormal_log.txt';
        // session_start();
        // $session_id = session_id();
        // session_write_close();
        $fp = fopen($log_file, 'a+');
        $prefix = date('y-m-d H:i:s') . ':';
        $log_content = is_array($log_content) ? json_encode($log_content) : $log_content;
        fwrite($fp, $prefix . $log_content . "\r\n");
        fclose($fp);
        return;
    }

    /**
     * 返回池 手机版用
     */
    static function result_pool($status, $result, $msg = '') {
        $data['status'] = strval($status);
        $data['result'] = $result;
        if (!empty($msg)) $data['msg'] = $msg;
        echo json_encode($data);
    }

    /**
     * 返回池
     */
    static function result_arr($status, $result, $msg = '') {
        $data['status'] = strval($status);
        $data['result'] = $result;
        if (!empty($msg)) $data['msg'] = $msg;
        return $data;
    }

    /**
     * 参数检测
     */
    static function params_check(array $param, array $_param) {
        foreach ($param as $k => $v) {
            if (!isset($_param[$v])) {
                echo self::result_pool(0, '参数错误!');
                exit;
            }
        }
    }

    /**
     * 友好时间
     * @param number $time
     */
    static function fdate($time) {
        if (!$time) return false;
        $fdate = '';
        $d = time() - intval($time);
        $byd = time() - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')); //前天
        $yd = time() - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
        $bz = time() - mktime(0, 0, 0, date('m'), date('d') - 7, date('Y')); //一周
        $dd = time() - mktime(0, 0, 0, date('m'), date('d'), date('Y')); //今天  
        switch ($d) {
            case $d <= $dd :
                $fdate = '今天';
                break;
            case $d <= $yd :
                $fdate = '昨天';
                break;
            case $d <= $byd :
                $fdate = '前天';
                break;
            // case $d <= $bz :
            //  $weekarray=array("日","一","二","三","四","五","六");
            //   $fdate = '星期'.$weekarray[date("w",$time)];
            //   break;
            default :
                $fdate = '曾经';
                break;
        }

        return $fdate;
    }

    /**
     * 多域名识别方案
     */
    static function getHost() {
        $url = $_SERVER['HTTP_HOST'];
        $data = explode('.', $url);
        $count = count($data);
        //判断是否是双后缀
        $is_two = true;
        $host_cn = array('com.cn', 'net.cn', 'org.cn', 'gov.cn');
        foreach ($host_cn as $host) {
            if (strpos($url, $host)) {
                $is_two = false;
            }
        }
        //如果是返回FALSE ，如果不是返回true
        if ($is_two == true) {
            $host = $data[$count - 2] . '.' . $data[$count - 1];
        } else {
            $host = $data[$count - 3] . '.' . $data[$count - 2] . '.' . $data[$count - 1];
        }
        return $host;
    }

}
