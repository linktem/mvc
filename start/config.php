<?php

//时区设置
date_default_timezone_set('PRC');

//数据库连接信息
$db_config = array(
    'host' => '127.0.0.1',
    'port' => 3306,
    'user' => 'root',
    'pwd' => '123456',
    'dbname' => 'test'
);

define('WEBSITE_ROOT_BASE', rtrim(str_replace('/start', '', str_replace('\\', '/', dirname(__FILE__))), '\/') . '/');
define('WEB_NAME', '我的网站');

ini_set('include_path', WEBSITE_ROOT_BASE . 'lib');
//业务逻辑层目录
define('PATH_CONTROLLERS', WEBSITE_ROOT_BASE . 'controllers/');
//数据处理层目录
define('PATH_MODELS', WEBSITE_ROOT_BASE . 'models/');
//视图层
define('PATH_VIEWS', WEBSITE_ROOT_BASE . 'views/');

define('DOMAIN_NAME', 'http://' . $_SERVER['HTTP_HOST']);
define('HTTP_UI', DOMAIN_NAME . '/ui/');
//加密KEY
//echo base64_encode(openssl_random_pseudo_bytes(16));exit;
define('KEY', 'T1sOdVU9+YjBjW7cHTcPvw==');
