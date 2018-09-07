<?php

/**
  +-----------------------------------------------------------------------------
 * 类包-分页类-YUI框架专用
  +-----------------------------------------------------------------------------
 * 专用于没有总条数的分页
 * @author 崔俊涛
 * @date 2017-11
 */
class YuiPagerNext {

    function urlAnalyze($page) {
        $url = $_SERVER['REQUEST_URI'];
        if (!isset($_GET['page'])) {
            $url_arr = explode('/', trim($url, '/'));
            if (count($url_arr) == 2) {
                $url .= '/page=PAGE';
            } else {
                $url .= '&page=PAGE';
            }
        } else {
            $url = str_replace('page=' . trim($_GET['page']), 'page=PAGE', $url);
        }

        return $url;
    }

    function getPager($page, $page_size, $current_page_num) {
        $page_data = array();
        if ($page - 1 > 0) {
            $page_data['prevPage'] = $page - 1;
        } else {
            $page_data['prevPage'] = 1;
        }
        $page_data['nextPage'] = $page + 1;

        $nextPageUrl = $this->urlAnalyze($page);

        $html = '<div class="pager_info">';
        $html .= '<a href="' . str_replace('PAGE', 1, $nextPageUrl) . '" class="yicon">&#xe679;</a>';
        if ($page > 1) {
            $page_data['prevPage'] = $page - 1;
        } else {
            $page_data['prevPage'] = 1;
        }
        $html .= '<a href="' . str_replace('PAGE', $page_data['prevPage'], $nextPageUrl) . '" class="yicon">&#xe618;</a>';

        //起始页码
        $p_start = $page - 5;
        $p_start = ($p_start <= 0) ? 1 : $p_start;

        for ($i = $p_start; $i <= $page; $i++) {
            if ($i == $page) {
                $html .= '<strong>' . $page . '</strong>';
            } elseif ($i > 0) {
                $html .= '<a href="' . str_replace('PAGE', $i, $nextPageUrl) . '">' . $i . '</a>';
            }
        }

        if ($page_size == $current_page_num) {
            $page_data['nextPage'] = $page + 1;
        } else {
            $page_data['nextPage'] = $page;
        }
        $html .= '<a href="' . str_replace('PAGE', $page_data['nextPage'], $nextPageUrl) . '" class="yicon">&#xe64f;</a>';
        $html .= '</div>';
        return $html;
    }

}
