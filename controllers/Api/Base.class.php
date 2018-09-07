<?php

/**
 * @author 林维
 * @date 2018-9-7
 */
class Base extends ApiController {

    function _run() {
        global $class, $action;
        parent::$current_module = 'Api';
        parent::$current_class = $class;
        parent::$current_action = $action;
        parent::requestAuth();
    }

}
