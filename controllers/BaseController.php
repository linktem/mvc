<?php

/**
 * @author 林维
 * @date 2018-9-6
 */
class BaseController {

    //API接口专用KEY
    static private $app_id = 1;
    static private $app_key = 'asdfkqj93054ursdgkfas';
    static protected $module = '';
    static protected $class = '';
    static protected $action = '';
    static protected $http_css = '';
    static protected $http_js = '';
    static protected $http_images = '';
    static protected $http_plugs = '';
    static protected $css_file = array();
    static protected $js_file_header = array();
    static protected $js_file_footer = array();
    static protected $seo = array(
        'title' => WEB_NAME,
        'keywords' => WEB_NAME,
        'description' => WEB_NAME
    );

    /**
     * 设置ui前缀
     */
    static protected function setUiPath() {
        self::$http_css = HTTP_UI . 'css/';
        self::$http_js = HTTP_UI . 'js/';
        self::$http_images = HTTP_UI . 'images/';
        self::$http_plugs = HTTP_UI . 'plugs/';

        self::$css_file = array(
            self::$http_plugs . 'yui/yui.pc.min.css',
        );
        self::$js_file_header = array(
            self::$http_js . 'jquery-3.2.1.min.js'
        );
        self::$js_file_footer = array(
            self::$http_plugs . 'yui/yui.pc.min.js'
        );
    }

    /**
     * 调用接口取数据或做数据处理请求
     * @param type $params
     * @return array 返回数组结构数据
     */
    static protected function requestApi($params) {
        //处理地址栏参数
        $url_arr = explode('/', trim($params['url'], '/'));
        $url = DOMAIN_NAME . "/index.php?M=Api&C={$url_arr[0]}&A={$url_arr[1]}";
        $url .= '&appkey=' . urlencode(LibBase::enCode(time() . '_' . self::$app_id . '_' . self::$app_key, KEY));
        unset($params['url']);
        $data = LibBase::curlPost($url, $params);
        return json_decode($data, true);
    }

    /**
     * API调试工具
     * @param array $params 要传递的参数
     */
    static protected function testApi($params) {
        //处理地址栏参数
        $url_arr = explode('/', trim($params['url'], '/'));
        $url = DOMAIN_NAME . "/index.php?M=Api&C={$url_arr[0]}&A={$url_arr[1]}";
        $url .= '&appkey=' . urlencode(LibBase::enCode(time() . '_' . self::$app_id . '_' . self::$app_key, KEY));
        unset($params['url']);
        $data = http_build_query($params);
        $opts = array(
            'http' => array(
                'method' => "POST",
                'header' => "Content-type: application/x-www-form-urlencoded\r\n" .
                "Content-length:" . strlen($data) . "\r\n",
                'content' => $data,
            )
        );
        $cxContext = stream_context_create($opts);

        $return = file_get_contents($url, false, $cxContext);
        echo '<pre>';
        $data2 = json_decode($return, true);
        if (json_last_error() > 0) {
            echo $return;
        } else {
            print_r($data2);
        }
        echo '</pre>';
        exit;
    }

    /**
      +-------------------------------------------------------------------------
     * 接收处理请求过来的数据
      +-------------------------------------------------------------------------
     * @param string $name 需要处理的表单名
     * @param string $type 类型:s=字符串,i=整型,a=数组,f=浮点型,d=双精度浮点型
     * @param string $way get | post
     * @param string $default_value 默认值
     * @param string $del_html 是否去除HTML标签,默认为是,y=是,n=否
     */
    static protected function request($name, $type = 's', $way = 'post', $default_value = '', $del_html = 'y') {
        $value = '';
        switch ($type) {
            case 's':
                if ($way == 'get') {
                    $value = isset($_GET[$name]) ? trim($_GET[$name]) : '';
                } else {
                    $value = isset($_POST[$name]) ? trim($_POST[$name]) : '';
                }
                if (empty($value) && strlen($default_value) > 0) {
                    $value = $default_value;
                }
                if ($del_html == 'y') {
                    $value = strip_tags($value);
                }
                break;
            case 'i':
                if ($way == 'get') {
                    $value = isset($_GET[$name]) ? intval($_GET[$name]) : '';
                } else {
                    $value = isset($_POST[$name]) ? intval($_POST[$name]) : '';
                }
                if ($value === '' && strlen($default_value) > 0) {
                    $value = $default_value;
                }
                if ($value === '' && strlen($default_value) == 0) {
                    $value = 0;
                }
                break;
            case 'f':
                if ($way == 'get') {
                    $value = isset($_GET[$name]) ? floatval($_GET[$name]) : 0;
                } else {
                    $value = isset($_POST[$name]) ? floatval($_POST[$name]) : 0;
                }
                if (empty($value) && strlen($default_value) > 0) {
                    $value = $default_value;
                }
                break;
            case 'd':
                if ($way == 'get') {
                    $value = isset($_GET[$name]) ? doubleval($_GET[$name]) : 0;
                } else {
                    $value = isset($_POST[$name]) ? doubleval($_POST[$name]) : 0;
                }
                if (empty($value) && strlen($default_value) > 0) {
                    $value = $default_value;
                }
                break;
            case 'a':
                if ($way == 'get') {
                    die('必须是POST方式,get方式不能发送数组.');
                }
                $value = isset($_POST[$name]) ? $_POST[$name] : array();
                if (empty($value) && strlen($default_value) > 0) {
                    $value = $default_value;
                } else {
                    $value = array_unique($value);
                    $value = array_diff($value, array(0));
                    if (strlen($default_value) > 0) {
                        array_unshift($value, $default_value);
                    }
                    $value = implode(',', $value);
                }
                break;
        }
        return $value;
    }

    /**
      +--------------------------------------------------------------------------
     * 获取一个不同状态的消息提示页面
      +--------------------------------------------------------------------------
     * @param string $status 状态:成功-success,失败-danger,温馨提示-warning
     * @param string $message 消息内容
     * @param string $href 链接地址
     * @param int $time 秒数
     */
    static protected function getMsg($status, $message, $href, $time = 5) {
        setcookie('msg[status]', $status, time() + 300, '/', DOMAIN_NAME);
        setcookie('msg[message]', $message, time() + 300, '/', DOMAIN_NAME);
        setcookie('msg[href]', $href, time() + 300, '/', DOMAIN_NAME);
        setcookie('msg[time]', $time, time() + 300, '/', DOMAIN_NAME);
        header('location: http://ke.7cbdn.com/message.php');
    }

    /**
     * 设置消息提示
     */
    static protected function setMsg($status, $msg, $data = []) {
        return array(
            'status' => $status,
            'msg' => $msg,
            'data' => $data
        );
    }

}
