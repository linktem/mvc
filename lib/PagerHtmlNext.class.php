<?php

/**
  +-----------------------------------------------------------------------------
 * 类包-分页类-伪静态网址
  +-----------------------------------------------------------------------------
 * 只适用于地址栏构造形式为：/page/1这种结构
 * @author 崔俊涛
 * @date 2016-04
 */
class PagerHtmlNext {

    function urlAnalyze($page) {
        $url = rtrim($_SERVER['REQUEST_URI'], '/');
        if (strpos($url, '/page/') === false) {
            $url = trim($url) . '/page/PAGE';
        } else {
            $url = str_replace("/page/$page", '/page/PAGE', $url);
        }
        return $url;
    }

    function getPager($total, $page, $pagesize) {
        if ($page > $total) {
            $page = 1;
        }
        $pageData = array();
        $pageData['pageCount'] = ceil($total / $pagesize);
        if (0 < $page - 1) {
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

        $html = '<div class="pager_info">';
        $html .= '<span class="total">共' . $total . '条/共' . $pageData['pageCount'] . '页</span>';
        $html .= '<a href="' . str_replace('PAGE', 1, $nextPageUrl) . '">首页</a>';
        $html .= '<a href="' . str_replace('PAGE', $pageData['prevPage'], $nextPageUrl) . '">上一页</a>';
        $html .= '<a href="' . str_replace('PAGE', $pageData['nextPage'], $nextPageUrl) . '">下一页</a>';
        $html .= '<a class="last" href="' . str_replace('PAGE', $pageData['lastPage'], $nextPageUrl) . '">末页</a>';
        $html .= '</div>';
        return $html;
    }

}