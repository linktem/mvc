<?php

/**
 * @author 林维
 * @date 2018-9-6
 */
class Index extends MainController {

    function __construct() {
        parent::_run();
    }

    function main() {
        $parame['url'] = '/Index/Main';
        $parame['form'] = array(
            'status' => 1
        );
        $result = parent::requestApi($parame);
        $list = $result['data'];
        include parent::display();
    }

}
