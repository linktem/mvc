<?php

require 'config.php';
require 'library.config.php';
require LIB_ROOT_BASE . '/DbConfig.class.php';
$module = isset($_GET['M']) ? ucwords(trim($_GET['M'])) : 'Main';
$class = isset($_GET['C']) ? ucwords(trim($_GET['C'])) : 'Index';
$action = isset($_GET['A']) ? trim($_GET['A']) : 'main';

//当前页面地址前缀,具体方法,在页面上使用的地方自定义
define('URL_CURRENT', '/index.php?M=' . $module . '&C=' . $class . '&A=');

if ($module == 'Api') {
    $class = isset($_GET['C']) ? ucwords(trim($_GET['C'])) : 'Test';
    $action = isset($_GET['A']) ? trim($_GET['A']) : 'testApi';
    require_once PATH_CONTROLLERS . 'ApiController.php';
    require_once PATH_CONTROLLERS . "Api/Base.class.php";
} else {
    require_once PATH_CONTROLLERS . 'BaseController.php';
    require_once PATH_CONTROLLERS . "{$module}Controller.php";
}
require_once PATH_CONTROLLERS . "$module/$class.class.php";

$obj = new $class();
if (!empty($action) && method_exists($obj, $action)) {
    $obj->$action();
}