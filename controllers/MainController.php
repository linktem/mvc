<?php

/**
 * @author 林维
 * @date 2018-9-6
 */
class MainController extends BaseController {

    protected function _run() {
        global $module, $class, $action;
        parent::$module = $module;
        parent::$class = $class;
        parent::$action = $action;
        parent::setUiPath();
    }

    /**
     * 载入视图模板
     * @param string $page_name 视图层模板文件名，不加后缀名称
     */
    protected function display($page_name = '') {
        $page_name = !empty($page_name) ? $page_name : parent::$action;
        return PATH_VIEWS . parent::$module . '/' . parent::$class . "/$page_name.php";
    }

}
