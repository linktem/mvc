<?php

/**
  +-----------------------------------------------------------------------------
 * PHP表单验证插件
  +-----------------------------------------------------------------------------
 * @author 崔俊涛
 * @date 2016-04
 */
class FormVerify {

    /**
      +-------------------------------------------------------------------------
     * 验证用户名,只允许下划线+汉字+英文+数字（不支持其它特殊字符）
      +-------------------------------------------------------------------------
     * @param string $value 表单值
     * @param int $min_len 最小长度
     * @param int $max_len 最长长度
     * @return boolean
     */
    static function isUsername($value, $min_len = 2, $max_len = 30) {
        if (!$value) {
            return false;
        }
        return preg_match('/^[_\w\d\x{4e00}-\x{9fa5}]{' . $min_len . ',' . $max_len . '}$/iu', $value);
    }

    /**
      +-------------------------------------------------------------------------
     * 验证是否为指定语言
      +-------------------------------------------------------------------------
     * @param string $value 表单值
     * @param string $charset 默认字符类别（en只能英文;cn只能汉字;alb数字;ALL不限制）
     * @param int $min_len 最小长度
     * @param int $max_len 最长长度
     * @return boolean
     */
    static function isLanguage($value, $charset = 'all', $min_len = 1, $max_len = 50) {
        if (!$value) {
            return false;
        }
        switch ($charset) {
            case 'en':
                $match = '/^[a-zA-Z]{' . $min_len . ',' . $max_len . '}$/iu';
                break;
            case 'cn':
                $match = '/^[\x{4e00}-\x{9fa5}]{' . $min_len . ',' . $max_len . '}$/iu';
                break;
            case 'alb':
                $match = '/^[0-9]{' . $min_len . ',' . $max_len . '}$/iu';
                break;
            case 'enalb':
                $match = '/^[a-zA-Z0-9]{' . $min_len . ',' . $max_len . '}$/iu';
                break;
            case 'all':
                $match = '/^[a-zA-Z0-9\x{4e00}-\x{9fa5}]{' . $min_len . ',' . $max_len . '}$/iu';
                break;
            //all限制为：只能是英文或者汉字或者数字的组合
        }
        return preg_match($match, $value);
    }

    /**
      +-------------------------------------------------------------------------
     * 验证密码
      +-------------------------------------------------------------------------
     * @param string $value 表单值
     * @param int $min_len 最小长度
     * @param int $max_len 最长长度
     * @return boolean
     */
    static function isPassword($value, $min_len = 6, $max_len = 16) {
        $match = '/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{' . $min_len . ',' . $max_len . '}$/i';
        $value = trim($value);
        if (!$value) {
            return false;
        }
        return preg_match($match, $value);
    }

    /**
      +-------------------------------------------------------------------------
     * 验证 Email
      +-------------------------------------------------------------------------
     * @param string $value 表单值
     * @param int $min_len 最小长度
     * @param int $max_len 最长长度
     * @param string $match 自定义正则表达式
     * @return boolean
     */
    static function isEmail($value, $min_len = 6, $max_len = 60, $match = '') {
        if (!$value) {
            return false;
        }
        if (empty($match)) {
            $match = '/^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/i';
        }
        return (strlen($value) >= $min_len && strlen($value) <= $max_len && preg_match($match, $value)) ? true : false;
    }

    /**
      +-------------------------------------------------------------------------
     * 货币金额格式化,小数点后最多2位
      +-------------------------------------------------------------------------
     * @param stirng $value 表单值
     * @return string
     */
    static function formatMoney($value) {
        return sprintf("%1\$.2f", $value);
    }

    /**
      +-------------------------------------------------------------------------
     * 验证电话号码
      +-------------------------------------------------------------------------
     * @param string $value 表单值
     * @param string $match 自定义正则表达式
     * @return boolean
     */
    static function isTelephone($value, $match = '') {
        //支持国际版：$match='/^[+]?([0-9]){1,3}?[ |-]?(0[1-9]{2,3})(-| )?\d{7,8}$/'
        if (!$value) {
            return false;
        }
        if (empty($match)) {
            $match = '/^(0[1-9]{2,3})(-| )?\d{7,8}$/';
        }
        return preg_match($match, $value);
    }

    /**
      +-------------------------------------------------------------------------
     * 验证手机
      +-------------------------------------------------------------------------
     * @param string $value 表单值
     * @param string $match 自定义正则表达式
     * @return boolean
     */
    static function isMobile($value, $match = '') {
        //支持国际版：([0-9]{1,5}|0)?1([3|4|5|8])+([0-9]){9,10}
        if (!$value) {
            return false;
        }
        if (empty($match)) {
            $match = '/^1[34578]{1}[0-9]{9}$/';
        }
        return preg_match($match, $value);
    }

    /**
      +-------------------------------------------------------------------------
     * 验证IP
      +-------------------------------------------------------------------------
     * @param string $value 表单值
     * @param string $match 自定义正则表达式
     * @return boolean
     */
    static function isIP($value, $match = '') {
        if (!$value) {
            return false;
        }
        if (empty($match)) {
            $match = '/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/';
        }
        return preg_match($match, $value);
    }

    /**
      +-------------------------------------------------------------------------
     * 验证身份证号码
      +-------------------------------------------------------------------------
     * @param string $value 表单值
     * @param string $match 自定义正则表达式
     * @return boolean
     */
    static function isIDcard($value, $match = '') {
        if (!$value) {
            return false;
        } else if (strlen($value) > 18) {
            return false;
        }
        if (empty($match)) {
            $match = '/^\d{6}((1[89])|(2\d))\d{2}((0\d)|(1[0-2]))((3[01])|([0-2]\d))\d{3}(\d|X)$/i';
        }
        return preg_match($match, $value);
    }

    /**
      +-------------------------------------------------------------------------
     * 验证URL
      +-------------------------------------------------------------------------
     * @param string $value 表单值
     * @param string $match 自定义正则表达式
     * @return boolean
     */
    static function isURL($value, $match = '') {
        $value = strtolower(trim($value));
        if (!$value) {
            return false;
        }
        if (empty($match)) {
            $match = '/^(http:\/\/)?(https:\/\/)?([\w\d-]+\.)+[\w-]+(\/[\d\w-.\/?%&=]*)?$/';
        }
        return preg_match($match, $value);
    }

    /**
     * 校验日期格式是否正确
     * 
     * @param string $date 日期
     * @param string $formats 需要检验的格式数组
     * @return boolean
     */
    function checkDateIsValid($date, $formats = array("Y-m-d", "Y/m/d")) {
        $unixTime = strtotime($date);
        if (!$unixTime) { //strtotime转换不对，日期格式显然不对。
            return false;
        }
        //校验日期的有效性，只要满足其中一个格式就OK
        foreach ($formats as $format) {
            if (date($format, $unixTime) == $date) {
                return true;
            }
        }

        return false;
    }

}
