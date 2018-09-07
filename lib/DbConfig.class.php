<?php

//系统维护总开关
define('SYSTEM_MAINTAIN', false);
//数据库错误显示开关
define('DB_DEBUG', true);

/**
 * 整站所有数据库配置文件
 * @author 崔俊涛
 * @date 2018-07
 */
class DbConfig {

    //数据库连接配置
    static private $db_config = array();

    /**
      +--------------------------------------------------------------------------
     * 主数据库
      +--------------------------------------------------------------------------
     */
    static protected function dbConn($db_name = '') {
        global $db_config;
        self::$db_config = $db_config;
        self::$db_config['dbname'] = $db_name ? $db_name : $db_config['dbname'];

        return new DbPdoMysql(self::$db_config);
    }

}
