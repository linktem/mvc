<?php
header("Content-type:text/html; charset=UTF-8");

//注销部分预定义常量，规范程序员代码写法
unset($_ENV, $HTTP_ENV_VARS, $_REQUEST, $HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_POST_FILES, $HTTP_COOKIE_VARS);

//目录常量
define('LIB_ROOT_BASE', rtrim(str_replace('\\', '/', dirname(__FILE__)), '\/'));

/**
 * 魔术方法，自动载入类文件
 * @param string $class_name 类名
 */
function yspAutoload($class_name) {
    $class_file_path = LIB_ROOT_BASE . "/$class_name.class.php";
    if (file_exists($class_file_path)) {
        require_once $class_file_path;
    }
}
spl_autoload_register('yspAutoload');

//当前页面URL
define('URL', "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
//当前页面的相对URL,不含域名
define('URI', $_SERVER['REQUEST_URI']);