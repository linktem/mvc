<?php

/**
  +-----------------------------------------------------------------------------
 * 类包-YUI专用分页类-伪静态网址
  +-----------------------------------------------------------------------------
 * 只适用于地址栏构造形式为：/page/1这种结构
 * @author 崔俊涛
 * @date 2016-04
 */
class YuiPagerHtml {

    function urlAnalyze($page) {
        $url = rtrim($_SERVER['REQUEST_URI'], '/');
        if (strpos($url, '/page/') === false) {
            $url = trim($url) . '/page/PAGE';
        } else {
            $url = str_replace("/page/$page", '/page/PAGE', $url);
        }
        return '/' . trim($url, '/');
    }

    function getPager($total, $page, $page_size) {
        if ($page > $total) {
            $page = 1;
        }
        $page_data = array();
        $page_data['pageCount'] = ceil($total / $page_size);
        if (0 < $page - 1) {
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

        $nextPageUrl = $this->urlAnalyze($page);

        $html = '<div class="pager_link"><span class="pager_text">总计' . $total . '条/每页' . $page_size . '条，共' . $page_data['pageCount'] . '页</span><div class="pager_info">';
        $html .= '<a href="' . str_replace('PAGE', 1, $nextPageUrl) . '">首页</a>';
        $html .= '<a href="' . str_replace('PAGE', $page_data['prevPage'], $nextPageUrl) . '">上一页</a>';

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

        $html .= '<a href="' . str_replace('PAGE', $page_data['nextPage'], $nextPageUrl) . '">下一页</a>';
        $html .= '<a class="last" href="' . str_replace('PAGE', $page_data['lastPage'], $nextPageUrl) . '">末页</a>';
        $html .= '</div></div>';
        return $html;
    }

}
