<?php

/**
  +-----------------------------------------------------------------------------
 * 类包-分页类-新整理
  +-----------------------------------------------------------------------------
 * @author 崔俊涛
 * @date 2017-02
 */
class PagerNew {

    /**
     * 地址栏参数处理
     * @param int $page 当前页码
     * @return string
     */
    function urlAnalyze($page) {
        $url = rtrim($_SERVER['REQUEST_URI'], '/');
        $parse_url = parse_url($url);
        $url_query = isset($parse_url['query']) ? $parse_url['query'] : '';
        if (!empty($url_query)) {
            if (strpos($url_query, 'page=') === false) {
                $url .= '&page=PAGE';
            } else {
                $url = str_replace('page=' . trim($page), 'page=PAGE', $url);
            }
        } else {
            $url .= '/index.php?page=PAGE';
        }
        return $url;
    }

    function getPager($total, $page, $pagesize) {
        $pageData = array();
        $pageData['pageCount'] = ceil($total / $pagesize);
        if ($page > $pageData['pageCount']) {
            $page = $pageData['pageCount'];
        }
        if ($page - 1 > 0) {
            $pageData['prevPage'] = $page - 1;
        } else {
            $pageData['prevPage'] = 1;
        }
        if ($pageData['pageCount'] >= $page + 1) {
            $pageData['nextPage'] = $page + 1;
        } else {
            $pageData['nextPage'] = $pageData['pageCount'];
        }
        $pageData['lastPage'] = $pageData['pageCount'];

        $nextPageUrl = $this->urlAnalyze($page);

        $html = '<div class="pager_info" id="pagerInfo">';
        $html .= '<span class="total">' . $total . '条/共' . $pageData['pageCount'] . '页</span>';
        $html .= '<a href="' . str_replace('PAGE', 1, $nextPageUrl) . '">首页</a>';
        $html .= '<a href="' . str_replace('PAGE', $pageData['prevPage'], $nextPageUrl) . '">上一页</a>';

        //起始页码
        $p_start = 1;
        //终止页码
        $p_end = 5;
        if ($page > $pageData['pageCount']) {
            $page = $pageData['pageCount'];
        }
        if ($page >= 3) {
            $p_end += ($page - 2) - 1;
        }
        if ($p_end > $pageData['pageCount']) {
            $p_end = $pageData['pageCount'];
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

        $html .= '<a href="' . str_replace('PAGE', $pageData['nextPage'], $nextPageUrl) . '">下一页</a>';
        $html .= '<a class="last" href="' . str_replace('PAGE', $pageData['lastPage'], $nextPageUrl) . '">末页</a>';
        $html .= '</div>';
        return $html;
    }

}
