<?php

/**
 * @author 林维
 * @date 2018-9-7
 */
class Index extends Base {

    function __construct() {
        parent::_run();
        parent::loadModel(__CLASS__);
    }

    function main() {
        $status = parent::formPost('status');
        $where = "WHERE status=$status";
        $list = $this->model->getList($where);
        if (count($list) > 0) {
            return parent::setMsg(1, '列表信息获取成功！', $list);
        }
        return parent::setMsg(0, '列表信息获取失败！');
    }

}
