<?php

/**
  +-----------------------------------------------------------------------------
 * 类包-cookie处理类
  +-----------------------------------------------------------------------------
 * @author 崔俊涛
 * @date 2016-04
 */
class CacheCookie extends LibBase {

    /**
      +--------------------------------------------------------------------------
     * 设置cookie加密
      +--------------------------------------------------------------------------
     * @param string $cookie_name 要加密的COOKIE名称
     * @param string $cookie_value 加密前的值
     * @param int $time 超时时间设置
     * @param string $folder COOKIE能访问的有效路径
     * @param string $domain COOKIE能访问的有效域名
     * @param string KEY 加密KEY，在配置文件配置
     */
    static public function setCookieInfo($cookie_name, $cookie_value, $time, $folder = '', $domain = '') {
        $value = parent::enCode($cookie_value, KEY);
        $domain = str_replace('http://', '', strtolower($domain));
        $domain_arr = explode(':', $domain);
        $domain = $domain_arr[0];
        setcookie($cookie_name, $value, $time, $folder, $domain);
    }

    /**
      +--------------------------------------------------------------------------
     * 设置cookie-不加密
      +--------------------------------------------------------------------------
     * @param string $cookie_name 要加密的COOKIE名称
     * @param string $cookie_value 加密前的值
     * @param int $time 超时时间设置
     * @param string $folder COOKIE能访问的有效路径
     * @param string $domain COOKIE能访问的有效域名
     * @param string KEY 加密KEY，在配置文件配置
     */
    static public function setOneCookie($cookie_name, $cookie_value, $time, $folder = '', $domain = '') {
        $domain = str_replace('http://', '', strtolower($domain));
        $domain_arr = explode(':', $domain);
        $domain = $domain_arr[0];
        setcookie($cookie_name, $cookie_value, $time, $folder, $domain);
    }

    /**
      +--------------------------------------------------------------------------
     * cookie解密
      +--------------------------------------------------------------------------
     * @param string $cookie_code 加密后的值
     * @param string $key 加密KEY
     * @return string 解密后的值
     */
    static public function getCookie($cookie_code) {
        return trim(parent::deCode($cookie_code, KEY));
    }

}
