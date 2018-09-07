<?php

/**
  +-----------------------------------------------------------------------------
 * 类包-分页类-YUI框架专用
  +-----------------------------------------------------------------------------
 * @author 崔俊涛
 * @date 2017-11
 */
class YuiPager {

    function urlAnalyze($page, $type) {
        $url = $_SERVER['REQUEST_URI'];
        if (!isset($_GET['page'])) {
            $url_arr = explode('/', trim($url, '/'));
            $expurl_num = $type == 'old' ? 3 : 2;
            if (count($url_arr) == $expurl_num) {
                $url .= '/page=PAGE';
            } else {
                $url .= '&page=PAGE';
            }
        } else {
            $url = str_replace('page=' . trim($_GET['page']), 'page=PAGE', $url);
        }

        return $url;
    }

    function getPager($total, $page, $page_size, $type = 'old') {
        $page_data = array();
        $page_data['pageCount'] = ceil($total / $page_size);
        if ($page > $page_data['pageCount']) {
            $page = $page_data['pageCount'];
        }
        if ($page - 1 > 0) {
            $page_data['prevPage'] = $page - 1;
        } else {
            $page_data['prevPage'] = 1;
        }
        if ($page_data['pageCount'] >= $page + 1) {
            $page_data['nextPage'] = $page + 1;
        } else {
            $page_data['nextPage'] = $page_data['pageCount'];
        }
        $page_data['lastPage'] = $page_data['pageCount'];

        $nextPageUrl = $this->urlAnalyze($page, $type);

        $html = '<div class="pager_link"><span class="pager_text">总计' . $total . '条/每页' . $page_size . '条，共' . $page_data['pageCount'] . '页</span><div class="pager_info">';
        $html .= '<a href="' . str_replace('PAGE', 1, $nextPageUrl) . '" class="yicon">&#xe679;</a>';
        $html .= '<a href="' . str_replace('PAGE', $page_data['prevPage'], $nextPageUrl) . '" class="yicon">&#xe618;</a>';

        //起始页码
        $p_start = 1;
        //终止页码
        $p_end = 5;
        if ($page > $page_data['pageCount']) {
            $page = $page_data['pageCount'];
        }
        if ($page >= 3) {
            $p_end += ($page - 2) - 1;
        }
        if ($p_end > $page_data['pageCount']) {
            $p_end = $page_data['pageCount'];
        }
        if ($p_end > 5) {
            $p_start = $p_end - 5 + 1;
        }

        for ($i = $p_start; $i <= $p_end; $i++) {
            if ($i == $page) {
                $html .= '<strong>' . $page . '</strong>';
            } elseif ($i > 0) {
                $html .= '<a href="' . str_replace('PAGE', $i, $nextPageUrl) . '">' . $i . '</a>';
            }
        }

        $html .= '<a href="' . str_replace('PAGE', $page_data['nextPage'], $nextPageUrl) . '" class="yicon">&#xe64f;</a>';
        $html .= '<a href="' . str_replace('PAGE', $page_data['lastPage'], $nextPageUrl) . '" class="yicon">&#xe67a;</a>';
        $html .= '</div></div>';
        return $html;
    }

}
