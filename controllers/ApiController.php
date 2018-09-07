<?php

/**
 * @author 林维
 * @date 2018-9-7
 */
class ApiController {

    //主模块基类名
    static protected $current_module = 'Api';
    //主业务逻辑类名
    static protected $current_class = '';
    //主业务逻辑类中的方法名
    static protected $current_action = '';
    //返回消息
    static protected $return_json = array(
        'status' => 1,
        'message' => '接口请求成功',
        'data' => array(),
    );
    static protected $app_keys = array(
        1 => 'asdfkqj93054ursdgkfas',
        2 => 'asdfkjhasdklfjasdlfjdsalk',
        3 => 'askdfjq93rujosdlirj3wes'
    );
    //模型层对象
    protected $model = null;

    /**
      +--------------------------------------------------------------------------
     * 载入指定的模型层
      +--------------------------------------------------------------------------
     * @param string $model
     * @return object
     */
    protected function loadModel($model = '', $return_model = false) {
        if (empty($model)) {
            $model = self::$module;
        }
        $model .= 'Model';
        require_once PATH_MODELS . "Api/$model.class.php";
        if ($return_model == true) {
            return new $model();
        } else {
            $this->model = new $model();
        }
    }

    /**
     * 初始化请求,访止报错
     */
    function testApi() {
        self::$return_json['status'] = 1;
        self::$return_json['message'] = 'API接口测试成功!';
    }

    /**
      +-------------------------------------------------------------------------
     * Api请求,最终必返回一种结果给调用的客户端
      +-------------------------------------------------------------------------
     */
    function __destruct() {
        echo json_encode(self::$return_json, JSON_UNESCAPED_UNICODE);
    }

    /**
      +-------------------------------------------------------------------------
     * 用户请求API的合法身份认证
      +-------------------------------------------------------------------------
     */
    static protected function requestAuth() {
        $appkey = isset($_GET['appkey']) ? trim($_GET['appkey']) : '';
        if (!empty($appkey)) {
            $key_code = LibBase::deCode($appkey, KEY);
            $key_code_arr = explode('_', $key_code);
            //前后加减5秒,不在范围内都属于接口调用失效
            if ($key_code_arr[0] - 5 > time() || $key_code_arr[0] + 5 < time()) {
                self::$return_json['status'] = 0;
                self::$return_json['message'] = '对不起,接口请求超时!';
                exit;
            }
            if (self::$app_keys[$key_code_arr[1]] == $key_code_arr[2]) {
                return true;
            }
        }
        self::$return_json['status'] = 0;
        self::$return_json['message'] = '对不起,您执行了一个非法请求!';
        exit;
    }

    /**
      +-------------------------------------------------------------------------
     * 接收处理POST过来的数据
      +-------------------------------------------------------------------------
     * @param string $name 需要处理的表单名
     * @param string $type 类型:s=字符串,i=整型,a=数组,f=浮点型,d=双精度浮点型
     * @param string $default_value 默认值
     * @param string $del_html 是否去除HTML标签,默认为是,y=是,n=否
     */
    static protected function formPost($name, $type = 's', $default_value = '', $del_html = 'y') {
        $value = '';
        switch ($type) {
            case 's':
                $value = isset($_POST['form'][$name]) ? trim($_POST['form'][$name]) : '';
                if (empty($value) && strlen($default_value) > 0) {
                    $value = $default_value;
                }
                if ($del_html == 'y') {
                    $value = strip_tags($value);
                }
                break;
            case 'i':
                $value = isset($_POST['form'][$name]) ? intval($_POST['form'][$name]) : '';
                if ($value === '' && strlen($default_value) > 0) {
                    $value = $default_value;
                }
                if ($value === '' && strlen($default_value) == 0) {
                    $value = 0;
                }
                break;
            case 'f':
                $value = isset($_POST['form'][$name]) ? floatval($_POST['form'][$name]) : 0;
                if (empty($value) && strlen($default_value) > 0) {
                    $value = $default_value;
                }
                break;
            case 'd':
                $value = isset($_POST['form'][$name]) ? doubleval($_POST['form'][$name]) : 0;
                if (empty($value) && strlen($default_value) > 0) {
                    $value = $default_value;
                }
                break;
            case 'a':
                $value = isset($_POST['form'][$name]) ? $_POST['form'][$name] : array();
                if (empty($value) && strlen($default_value) > 0) {
                    $value = $default_value;
                }
                break;
        }
        return $value;
    }

    /**
      +-------------------------------------------------------------------------
     * 设置消息返回格式
      +-------------------------------------------------------------------------
     * @param int $status 状态:0=失败,1=成功
     * @param string $message 消息内容
     * @param array $data 附加数据
     */
    static function setMsg($status, $message, $data = array()) {
        self::$return_json = array(
            'status' => $status,
            'message' => $message,
            'data' => $data
        );
        return self::$return_json;
    }

}
